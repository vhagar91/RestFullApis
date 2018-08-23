<?php

/**
 * Description of OwnershipStatuses
 */

namespace RestBundle\Helpers;

class BackendModuleName {

    const NO_MODULE = 0;
    const MODULE_DESTINATION = 1;
    const MODULE_FAQS = 2;
    const MODULE_ALBUM = 3;
    const MODULE_OWNERSHIP = 4;
    const MODULE_CURRENCY = 5;
    const MODULE_LANGUAGE = 6;
    const MODULE_RESERVATION = 7;
    const MODULE_USER = 8;
    const MODULE_GENERAL_INFORMATION = 9;
    const MODULE_COMMENT = 10;
    const MODULE_UNAVAILABILITY_DETAILS = 11;
    const MODULE_METATAGS = 12;
    const MODULE_MUNICIPALITY = 13;
    const MODULE_SEASON = 14;
    const MODULE_LODGING_RESERVATION = 15;
    const MODULE_LODGING_COMMENT = 16;
    const MODULE_LODGING_OWNERSHIP = 17;
    const MODULE_LODGING_USER = 18;
    const MODULE_MAIL_LIST = 19;
    const MODULE_BATCH_PROCESS = 20;
    const MODULE_CLIENT_MESSAGES = 21;
    const MODULE_CLIENT_COMMENTS = 22;
    const MODULE_AWARD = 23;
    const MODULE_RBAC = 24;
    const MODULE_ACCOMMODATION_PAYMENT = 25;

    public static function getModuleName($module_number)
    {
        switch ($module_number) {
            case BackendModuleName::MODULE_DESTINATION: return "Destinos";
            case BackendModuleName::MODULE_FAQS: return "FAQ";
            case BackendModuleName::MODULE_ALBUM: return "Album";
            case BackendModuleName::MODULE_OWNERSHIP: return "Alojamientos";
            case BackendModuleName::MODULE_CURRENCY: return "Monedas";
            case BackendModuleName::MODULE_LANGUAGE: return "Lenguajes";
            case BackendModuleName::MODULE_RESERVATION: return "Reservaciones";
            case BackendModuleName::MODULE_USER: return "Usuarios";
            case BackendModuleName::MODULE_GENERAL_INFORMATION: return "Información General";
            case BackendModuleName::MODULE_COMMENT: return "Comentarios";
            case BackendModuleName::MODULE_UNAVAILABILITY_DETAILS: return "Disponibilidad";
            case BackendModuleName::MODULE_METATAGS: return "Meta Tags";
            case BackendModuleName::MODULE_MUNICIPALITY: return "Municipios";
            case BackendModuleName::MODULE_SEASON: return "Temporadas";
            case BackendModuleName::MODULE_LODGING_RESERVATION: return "Módulo Casa - Reservaciones";
            case BackendModuleName::MODULE_LODGING_COMMENT: return "Módulo Casa - Comentarios";
            case BackendModuleName::MODULE_LODGING_OWNERSHIP: return "Módulo Casa - MyCasa";
            case BackendModuleName::MODULE_LODGING_USER: return "Módulo Casa - Perfil de Usuario";
            case BackendModuleName::MODULE_MAIL_LIST: return "Listas de correo";
            case BackendModuleName::MODULE_BATCH_PROCESS: return "Procesamiento por lotes";
            case BackendModuleName::MODULE_CLIENT_MESSAGES: return "Mensajes a los clientes";
            case BackendModuleName::MODULE_CLIENT_COMMENTS: return "Comentarios de los clientes";
            case BackendModuleName::MODULE_AWARD: return "Premios";
            case BackendModuleName::MODULE_RBAC: return "Roles y permisos";
            case BackendModuleName::MODULE_ACCOMMODATION_PAYMENT: return "Pagos Alojamientos";

            default: return "MyCP";
        }
    }

}

class DataBaseTables {

    const USER = "user";
    const METATAGS = "metatag";
    const MUNICIPALITY = "municipality";
    const DESTINATION = "destination";
    const DESTINATION_CATEGORY = "destinationCategory";
    const DESTINATION_PHOTO = "destinationPhoto";
    const OWNERSHIP = "ownership";
    const OWNERSHIP_PHOTO = "ownershipPhoto";
    const ROOM = "room";
    const BATCH_PROCESS = "batchProcess";
    const UNAVAILABILITY_DETAILS = "unavailabilityDetails";
    const ALBUM_CATEGORY = "albumCategory";
    const ALBUM = "album";
    const ALBUM_PHOTO = "albumPhoto";
    const FAQ_CATEGORY = "faqCategory";
    const FAQ_LANG = "faqLang";
    const FAQ = "faq";
    const INFORMATION = "information";
    const INFORMATION_LANG = "informationLang";
    const COMMENT = "comment";
    const AWARD = "award";
    const CLIENT_COMMENT = "clientComment";
    const MESSAGE = "message";
    const GENERAL_RESERVATION = "generalReservation";
    const ROLE = "role";
    const CURRENCY = "currency";
    const LANGUAGE = "lang";
    const SEASON = "season";
    const MAIL_LIST = "mailList";
    const MAIL_LIST_USER = "mailListUser";
    const MAIL_ROLE_PERMISSION = "rolePermission";
    const ACCOMMODATION_PAYMENT = "ownershipPayment";
}
?>
