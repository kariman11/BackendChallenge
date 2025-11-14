<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Email", description: "Email verification")]
class EmailEndpoints
{
    #[OA\Get(
        path: "/api/email/verify/{id}/{hash}",
        tags: ["Email"],
        summary: "Verify email via signed URL",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "hash", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Email verified"),
            new OA\Response(response: 403, description: "Invalid or expired link")
        ]
    )]
    public function verify() {}

    #[OA\Post(
        path: "/api/email/resend",
        tags: ["Email"],
        summary: "Resend verification link",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [ new OA\Property(property: "email", type: "string") ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Sent") ]
    )]
    public function resend() {}
}
