<?php


namespace AbmmHasan\Toolbox\Tonic;


trait Common
{
    /**
     * Prevents cloning the instances.
     *
     * @return void
     */
    public function __clone()
    {
    }

    /**
     * Prevents unserializing the instances.
     *
     * @return void
     */
    public function __wakeup()
    {
    }
}
