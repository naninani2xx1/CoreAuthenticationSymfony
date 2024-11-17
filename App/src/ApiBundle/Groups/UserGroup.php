<?php 

namespace App\ApiBundle\Groups;

class UserGroup {
    const USER_LIST = "user:list";
    const USER_ITEM = "user:item";
    const USER_POST_WRITE_ITEM = "user:post:write:item";
    const USER_POST_READ_ITEM = "user:post:read:item";
    const USER_PATCH_ITEM = "user:patch:item";
    const USER_AUTH_ITEM = "user:auth:item";
}