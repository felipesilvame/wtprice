<?php

namespace App\Models\Auth;

use App\Models\Auth\Traits\Scope\UserScope;
use App\Models\Auth\Traits\Method\UserMethod;
use App\Models\Auth\Traits\Attribute\UserAttribute;
use App\Models\Auth\Traits\Relationship\UserRelationship;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User.
 */
class User extends BaseUser implements JWTSubject
{
    use UserAttribute,
        UserMethod,
        UserRelationship,
        UserScope;

    // Specify Slack Webhook URL to route notifications to
    public function routeNotificationForSlack() {
      return env('SLACK_WEBHOOK_URL');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
