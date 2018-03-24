<?php

interface IGovnokodUsersSource {
    /**
     * @param int|string $user_id
     * @return GovnokodRuAuthorModel|WP_Error|null
     */
    public function loadUser($user_id);
}