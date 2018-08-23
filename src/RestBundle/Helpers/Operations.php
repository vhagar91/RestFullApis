<?php

/**
 * Description of Operations
 */

namespace RestBundle\Helpers;

class Operations {

    const SAVE_AND_EXIT = 0;
    const SAVE_AND_NEW = 1;
    const SAVE_AND_ADD_PHOTOS = 2;
    const SAVE_AND_PUBLISH_ACCOMMODATION = 3;
    const CONTACT_FORM_RECEIVE_INSTRUCTIONS = 4;
    const SAVE_USER_AND_NEW_OFFER = 5;
    const SAVE_OFFER_AND_SEND = 6;
    const SAVE_OFFER_AND_VIEW = 7;
    const SAVE = 8;
    const SAVE_AND_UPDATE_CALENDAR = 9;
    const UPDATE_PRICES = 10;

}

class FormMode{
    const FORM_MODE_INSERT = 1;
    const FORM_MODE_EDIT = 2;
}





?>
