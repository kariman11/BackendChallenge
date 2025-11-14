<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "MagicLink", description: "Passwordless magic links")]
class MagicLinkEndpoints
{
    #[OA\Post(
        path: "/api/magic",
        tags: ["MagicLink"],
        summary: "Request a magic login link",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [ new OA\Property(property: "email", type: "string") ])
        ),
        responses: [ new OA\Response(response: 200, description: "Sent") ]
    )]
    public function request() {}

    #[OA\Get(
        path: "/api/magic/consume/{token}",
        tags: ["MagicLink"],
        summary: "Consume magic link token",
        parameters: [ new OA\Parameter(name: "token", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Token consumed and JWT returned") ]
    )]
    public function consume() {}
}
