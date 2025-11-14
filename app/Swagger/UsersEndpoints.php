<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Users", description: "User lifecycle and management")]
class UsersEndpoints
{
    #[OA\Get(
        path: "/api/users",
        tags: ["Users"],
        summary: "List users (cursor pagination + RSQL filters)",
        security: [["BearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "filter", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "fields", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "include", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [ new OA\Response(response: 200, description: "OK") ]
    )]
    public function listUsers() {}

    #[OA\Post(
        path: "/api/users/{id}/export",
        tags: ["Users"],
        summary: "Request GDPR export for user",
        security: [["BearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Export queued")
        ]
    )]
    public function exportUser() {}


    #[OA\Post(
        path: "/api/users/gdpr/request-delete",
        tags: ["Users"],
        summary: "Request GDPR delete (creates request)",
        security: [["BearerAuth" => []]],
        responses: [ new OA\Response(response: 200, description: "Request created") ]
    )]
    public function requestGdprDelete() {}
}
