<?php

namespace Marshmallow\LaravelFacebookWebhook\SignatureValidator;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;

class FacebookSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->header($config->signatureHeaderName);
        $signingSecret = $config->signingSecret;

        if (
            strlen($signature) == 45 &&
            substr($signature, 0, 5) == 'sha1='
        ) {
            $signature = substr($signature, 5);
        }

        if (!$signature) {
            return false;
        }

        $signingSecret = $config->signingSecret;

        if (empty($signingSecret)) {
            throw WebhookFailed::signingSecretNotSet();
        }

        $computedSignature = hash_hmac('sha1', $request->getContent(), $signingSecret);

        return hash_equals($signature, $computedSignature);
    }
}
