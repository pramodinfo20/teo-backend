<?php

namespace App\Controller;

use App\Middleware\Middleware;

trait SpringBaseTrait
{
    protected function springGet($path, $options = [])
    {
        return $this->getSpringMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_GET, $path,
                array_merge(['auth' => ['admin', 'adminPass']], $options))
            ->sentRequest();
    }

    /* ------------- Spring - GET ----------------- */

    abstract protected function getSpringMiddleware();
    /* ------------------------------------------- */

    /* ------------- Spring - POST ----------------- */

    protected function springPost($path, $options = [])
    {
        return $this->getSpringMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_POST, $path,
                array_merge(['auth' => ['admin', 'adminPass']], $options))
            ->sentRequest();
    }
    /* ------------------------------------------- */

    /* ------------- Spring - Files ----------------- */
    protected function springFiles($path, $options = [])
    {
        return $this->getSpringMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_POST_FILES, $path,
                array_merge(['auth' => ['admin', 'adminPass']], $options))
            ->sentRequest();
    }
    /* ------------------------------------------- */

    /* ------------- Spring - DELETE ----------------- */
    protected function springDelete($path, $options = [])
    {
        return $this->getSpringMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_DELETE, $path,
                array_merge(['auth' => ['admin', 'adminPass']], $options))
            ->sentRequest();
    }
    /* ------------------------------------------- */
}