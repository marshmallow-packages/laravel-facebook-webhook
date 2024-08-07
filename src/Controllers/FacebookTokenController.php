<?php

namespace Marshmallow\LaravelFacebookWebhook\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Marshmallow\LaravelFacebookWebhook\Models\FacebookToken;
use Marshmallow\LaravelFacebookWebhook\Models\WebhookLeadResponse;
use Marshmallow\LaravelFacebookWebhook\Events\FacebookTokenCreated;
use Marshmallow\LaravelFacebookWebhook\Events\FacebookWebhookReceived;

class FacebookTokenController extends \App\Http\Controllers\Controller
{
    /**
     * The base Facebook Graph URL.
     *
     * @var string
     */
    protected $graphUrl = 'https://graph.facebook.com';

    /**
     * The Graph API version for the request.
     *
     * @var string
     */
    protected $version;

    /**
     * The access token that was last used.
     *
     * @var string|null
     */
    protected $lastToken;

    /**
     * The HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $facebook;

    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The page ID.
     *
     * @var int
     */
    protected $pageId;

    /**
     * The page .
     *
     */
    protected $page;

    /**
     * The callback url for the webhook.
     *
     */
    protected $callback_route;

    protected $config;


    /**
     * Redirects the user to the Facebook authentication page.
     *
     */
    public function __construct()
    {
        $this->config       = config('facebook-webhook.configs.0');
        $this->pageId       = $this->config['page_id'];
        $this->clientId     = $this->config['app_id'];
        $this->clientSecret = $this->config['app_secret'];
        $this->version      = $this->config['graph_api_version'];
        $this->callback_route = $this->config['callback_route'];
        $this->lastToken    = $this->getLastToken();
    }

    protected function getLastToken()
    {
        $token = FacebookToken::where([
            'access_type' => 'page',
            'facebook_id' => $this->pageId
        ])->latest()->first();

        if (isset($token->access_token)) {
            return $token->access_token;
        }

        return;
    }


    protected function getBaseUri()
    {
        return $this->graphUrl . '/' . $this->version;
    }

    protected function getTokenUrl()
    {
        return $this->getBaseUri() . '/oauth/access_token';
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];

        return $fields;
    }


    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function facebook($method, $url, $options = [], $paginate = false)
    {
        if (!Str::startsWith($url, $this->graphUrl)) {
            $url = $this->getBaseUri() . $url;
            $options = Arr::add($options, 'access_token', $this->lastToken);
        }

        if ($options) {
            $response = Http::$method($url, $options);
        } else {
            $response = Http::$method($url);
        }

        if ($response->successful()) {
            $data = $response->json();
            if ($paginate) {
                return $data;
            }
            if (isset($data['data'])) {
                return $data['data'];
            }
            return $data;
        } else {
            $response->throw();
        }
    }


    protected function facebookPaginate($method, $url, $options = [], $previousData = [], $offset = 0)
    {
        $response = $this->facebook(
            method: $method,
            url: $url,
            options: $options,
            paginate: true
        );

        $reponseData = $previousData;
        $data = Arr::get($response, 'data');
        $next = Arr::get($response, 'paging.next');

        if ($data) {
            $reponseData = array_merge($previousData, $data);
        }

        if ($next) {
            return $this->facebookPaginate(
                method: $method,
                url: $next,
                previousData: $reponseData,
            );
        }

        return $reponseData;
    }

    public function installApp()
    {
        $endpoint = "/{$this->pageId}/subscribed_apps";
        $fields = ['subscribed_fields' => 'leadgen'];
        $response = $this->facebook('POST', $endpoint, $fields);
        if (!$response['success']) {
            throw new \Exception("Error installing App on Page", 1);
        }

        return true;
    }

    public function installWebhook()
    {
        if (!Route::has($this->callback_route)) {
            throw new \Exception("Callback Route does not exists", 1);
        };

        $this->lastToken = $this->clientId . '|' . $this->clientSecret;
        $endpoint = "/{$this->clientId}/subscriptions";

        $fields = [
            'object' => 'page',
            'fields' => 'leadgen',
            'callback_url' => route($this->callback_route),
            'verify_token' => $this->clientSecret
        ];
        $response = $this->facebook('POST', $endpoint, $fields);

        if (!$response['success']) {
            throw new \Exception("Error installing App on Page", 1);
        }

        return true;
    }

    /**
     * Redirects the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function getRedirectUrl()
    {
        return Socialite::driver('facebook')->scopes(
            ['pages_read_engagement', 'leads_retrieval', 'pages_manage_metadata', 'pages_manage_ads', 'pages_show_list', 'ads_management']
        )->stateless()->redirect()->getTargetUrl();
    }

    public function setPageAccessToken($page_access_token)
    {
        $token_data = $this->facebook('GET', "/debug_token", ['input_token' => $page_access_token]);

        if (empty($token_data['expires_at'])) {
            $token_expires_in = now()->addSeconds($token_data['data_access_expires_at']);
            $long_lived_token = $page_access_token;
        } else {
            $token_expires_in = now()->addSeconds($token_data['expires_at']);
            $long_lived_token = null;
        }
        $type = 'app';
        if (isset($token_data['profile_id'])) {
            $facebook_id = $token_data['profile_id'];
            $type = 'page';
        } elseif (isset($token_data['user_id'])) {
            $facebook_id = $token_data['user_id'];
            $type = 'user';
        } elseif (isset($token_data['app_id'])) {
            $facebook_id = $token_data['app_id'];
        } else {
            $facebook_id = $this->app['id'];
        }

        $facebook_token = FacebookToken::updateOrCreate([
            'facebook_id' => $facebook_id,
            'access_type' => $type,
        ], [
            'access_token' => $page_access_token,
            'refresh_token' => null,
            'token_type' => $token_data['type'],
            'long_lived_token' => $long_lived_token,
            'expires_at' => $token_expires_in,
        ]);

        FacebookTokenCreated::dispatch($facebook_token);
    }

    public function getPageData(FacebookToken $token, $pageId)
    {
        $this->lastToken = $token->access_token;
        $url = "/{$token->facebook_id}/accounts";
        $pages = $this->facebook('GET', $url);

        foreach ($pages as $page) {
            if ($page['id'] == $pageId) {
                return $page;
            }
        }
    }

    public function getLeadData($lead_id)
    {
        $url = "/" . $lead_id;
        return $this->facebook('GET', $url);
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return JsonResponse
     */
    public function handleProviderCallback(Request $request)
    {
        try {
            $code               = $request->query()['code'];
            $token_response     = Socialite::driver('facebook')->getAccessTokenResponse($code);
            $access_token       = $token_response['access_token'];
            $facebook_user      = Socialite::driver('facebook')->userFromToken($access_token);
        } catch (\Exception $exception) {
            throw new \Exception('Something went wrong with authentication on Facebook', 2);
        }

        if (empty($token_response['expires_in'])) {
            $token_expires_in = now()->addMonths(6);
            $long_lived_token = $access_token;
        } else {
            $token_expires_in = now()->addSeconds($token_response['expires_in']);
            $long_lived_token = null;
        }

        $this->lastToken = $access_token;

        $facebook_token = FacebookToken::updateOrCreate([
            'facebook_id' => $facebook_user->id,
            'access_type' => 'user',
        ], [
            'access_token' => $access_token,
            'refresh_token' => $facebook_user->refreshToken,
            'token_type' => $token_response['token_type'],
            'long_lived_token' => $long_lived_token,
            'expires_at' => $token_expires_in,
        ]);

        FacebookTokenCreated::dispatch($facebook_token);

        $page = $this->getPageData($facebook_token, $this->pageId);
        $this->setPageAccessToken($page['access_token']);
        $this->lastToken = $page['access_token'];

        return 'Connection succesfull, please return to console';
    }

    public function storeWebhookResponse($webhookResponseData, $type)
    {
        if (
            !isset($webhookResponseData['field']) ||
            $webhookResponseData['field'] !== 'leadgen'
        ) {
            throw new \Exception('Invalid entry type');
        }

        if (
            !isset($webhookResponseData['value'])
        ) {
            throw new \Exception('Empty response recieved');
        }

        $webhook_data = $webhookResponseData['value'];
        $webhook_data['type'] = $type;
        $webhook_data['received_at'] = Carbon::parse($webhook_data['created_time']);

        unset($webhook_data['created_time']);

        $lead_id = $webhook_data['leadgen_id'];
        if ($lead_id !== "444444444444") {
            $webhook_data['payload'] = json_encode($this->getLeadData($lead_id));
        }

        $webhookLeadResponse = WebhookLeadResponse::updateOrCreate([
            'leadgen_id' => $lead_id,
            'type' => $type,
        ], $webhook_data);

        $this->processFacebookLead($webhookLeadResponse);
    }

    public function verifyWebhookInstall(Request $request)
    {
        $content = $request->all();
        $challengeToken = $this->clientSecret;
        if (
            isset($content['hub_verify_token']) && $content['hub_verify_token'] == $challengeToken
        ) {
            $returnToken = $content['hub_challenge'];
        } else {
            $returnToken = 'Invalid Verification';
        }
        echo $returnToken;
    }


    protected function processFacebookLead(WebhookLeadResponse $webhookLeadResponse): void
    {
        $jobclass = $this->config['process_facebook_webhook_job'];
        $job = new $jobclass($webhookLeadResponse);
        dispatch($job)->delay(now()->addMinutes(1));
        FacebookWebhookReceived::dispatch($webhookLeadResponse);
    }

    /**
     * Obtain the forms & leads from Facebook API.
     */
    public function getForms()
    {
        $url = "/" . $this->pageId . "/leadgen_forms";
        return $this->facebookPaginate('GET', $url);
    }

    public function getLeadsByForm($form_id, $offsetDays = 1, $processLeads = true)
    {
        $url = "/" . $form_id . "/leads";

        $fields = [
            'id',
            'created_time',
            'field_data',
            'adgroup_id',
            'ad_id',
            'form_id',
        ];

        $time = Carbon::now()->subDays($offsetDays)->startOfDay()->timestamp;

        $options = [
            'fields' => implode(',', $fields),
            'limit' => 50,
            'filtering' => [
                [
                    'field' => 'time_created',
                    'operator' => 'GREATER_THAN_OR_EQUAL',
                    'value' => $time,
                ],
            ],
        ];

        $api_leads = $this->facebookPaginate(
            method: 'GET',
            url: $url,
            options: $options,
        );

        collect($api_leads)->map(function ($api_lead) use ($processLeads) {
            $this->storeApiResponse($api_lead, $processLeads);
        });
    }

    public function storeApiResponse($api_data, $processLead = true)
    {
        $type = 'facebook-lead';
        $lead_id = $api_data['id'];
        $payload = $api_data;

        $api_data['type'] = $type;
        $api_data['received_at'] = Carbon::parse($api_data['created_time']);
        $api_data['leadgen_id'] = $lead_id;
        $api_data['page_id'] = $this->pageId;

        unset($api_data['field_data']);
        unset($api_data['id']);
        unset($api_data['created_time']);

        if ($lead_id !== "444444444444") {
            $api_data['payload'] = json_encode($payload);
        }

        $leadResponse = WebhookLeadResponse::firstOrCreate([
            'leadgen_id' => $lead_id,
            // 'ad_id' => $api_data['ad_id'],
            'form_id' => $api_data['form_id'],
            'type' => $type,
        ], $api_data);

        if ($processLead) {
            $this->processFacebookLead($leadResponse);
        }
    }
}
