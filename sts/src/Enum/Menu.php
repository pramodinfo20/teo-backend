<?php

namespace App\Enum;

class Menu
{
    const MODE_VIEW = 1;
    const MODE_EDIT = 2;
    const MODE_CHANGE_ORDER = 3;
    const MODE_ADD_NEW_PROPERTY = 4;
    const MODE_COPY_TO_ANOTHER = 5;
    const CONFIGURATION_STATE_UNDER_DEVELOPMENT = 1;

    // Release status
    const RELEASE_STATUS_IN_DEVELOPMENT = 1;
    const RELEASE_STATUS_RELEASED = 2;
    const RELEASE_STATUS_TESTING = 3;

}