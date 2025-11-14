<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Invitations", description: "Invitation management")]
class InvitationsEndpoints
{
    #[OA\Get(
        path: "/invitations/accept",
        tags: ["Invitations"],
        summary: "Web page for invitation acceptance (optional)",
        parameters: [ new OA\Parameter(name: "token", in: "query", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "HTML page") ]
    )]
    public function acceptPage() {}
}
