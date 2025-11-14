<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "2FA", description: "Two Factor Authentication (TOTP)")]
class TwoFactorEndpoints
{
    #[OA\Post(
        path: "/api/2fa/setup",
        tags: ["2FA"],
        summary: "Generate 2FA secret and QR",
        security: [["BearerAuth" => []]],
        responses: [ new OA\Response(response: 200, description: "Secret generated") ]
    )]
    public function setup() {}

    #[OA\Post(
        path: "/api/2fa/enable",
        tags: ["2FA"],
        summary: "Enable 2FA with OTP",
        security: [["BearerAuth" => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [ new OA\Property(property: "otp", type: "string") ])
        ),
        responses: [ new OA\Response(response: 200, description: "Enabled") ]
    )]
    public function enable() {}

    #[OA\Post(
        path: "/api/2fa/disable",
        tags: ["2FA"],
        summary: "Disable 2FA",
        security: [["BearerAuth" => []]],
        responses: [ new OA\Response(response: 200, description: "Disabled") ]
    )]
    public function disable() {}
}
