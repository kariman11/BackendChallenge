<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Organizations", description: "Organization management")]
class OrganizationsEndpoints
{
    #[OA\Post(
        path: "/api/orgs",
        tags: ["Organizations"],
        summary: "Create organization",
        security: [["BearerAuth" => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [ new OA\Property(property: "name", type: "string") ])),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function create() {}

    #[OA\Get(
        path: "/api/orgs",
        tags: ["Organizations"],
        summary: "List user's organizations",
        security: [["BearerAuth" => []]],
        responses: [ new OA\Response(response: 200, description: "OK") ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/orgs/{org}/add-member",
        tags: ["Organizations"],
        summary: "Invite an email to organization (creates invitation)",
        security: [["BearerAuth" => []]],
        parameters: [ new OA\Parameter(name: "org", in: "path", required: true, schema: new OA\Schema(type: "integer")) ],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "email", type: "string"),
            new OA\Property(property: "role", type: "string")
        ])),
        responses: [ new OA\Response(response: 200, description: "Invitation sent") ]
    )]
    public function addMember() {}

    #[OA\Post(
        path: "/api/orgs/{org}/invites/accept/{token}",
        tags: ["Organizations"],
        summary: "Accept organization invitation",
        parameters: [
            new OA\Parameter(name: "org", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "token", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [ new OA\Response(response: 200, description: "Joined org") ]
    )]
    public function acceptInvite() {}
}
