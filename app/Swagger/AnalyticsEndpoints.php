<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class AnalyticsEndpoints
{
    #[OA\Get(
        path: "/api/users/top-logins",
        tags: ["Analytics"],
        summary: "Top users by login activity",
        security: [["BearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "window", in: "query", schema: new OA\Schema(type: "string", example: "7d")),
            new OA\Parameter(name: "org_id", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function topLogins() {}


    #[OA\Get(
        path: "/api/users/inactive",
        tags: ["Analytics"],
        summary: "Inactive users report",
        security: [["BearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "window", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "org_id", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function inactive() {}
}
