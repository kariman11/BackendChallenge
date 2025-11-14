<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "GDPR", description: "Exports & Delete Requests")]
class GdprEndpoints
{
    #[OA\Get(
        path: "/api/users/export/download/{token}",
        tags: ["GDPR"],
        summary: "Download GDPR export (one-time token)",
        parameters: [ new OA\Parameter(name: "token", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "File download") ]
    )]
    public function downloadExport() {}

//    #[OA\Post(
//        path: "/api/users/{id}/export",
//        tags: ["GDPR"],
//        summary: "Request GDPR export for user",
//        security: [["BearerAuth" => []]],
//        parameters: [ new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")) ],
//        responses: [ new OA\Response(response: 200, description: "Export queued") ]
//    )]
//    public function requestExport() {}

    #[OA\Post(
        path: "/api/users/gdpr/{id}/approve",
        tags: ["GDPR"],
        summary: "Approve GDPR delete request (admin)",
        security: [["BearerAuth" => []]],
        parameters: [ new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")) ],
        responses: [ new OA\Response(response: 200, description: "Approved") ]
    )]
    public function approveDelete() {}

    #[OA\Post(
        path: "/api/users/gdpr/{id}/reject",
        tags: ["GDPR"],
        summary: "Reject GDPR delete request",
        security: [["BearerAuth" => []]],
        parameters: [ new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")) ],
        responses: [ new OA\Response(response: 200, description: "Rejected") ]
    )]
    public function rejectDelete() {}
}
