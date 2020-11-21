<?php


namespace Jet\Service;


interface Service
{
    /**
     * @return void
     */
    function onCreate();

    /**
     * @return void
     */
    function onCall();
}