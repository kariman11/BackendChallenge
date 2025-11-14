<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "email", type: "string"),
        new OA\Property(property: "email_verified_at", type: "string", nullable: true),
        new OA\Property(property: "two_factor_enabled", type: "boolean"),
        new OA\Property(property: "last_login_at", type: "string", nullable: true),
        new OA\Property(property: "login_count", type: "integer"),
    ]
)]
class UserSchema {}
