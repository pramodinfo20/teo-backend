<?php
    trait SpringBaseTrait {
        abstract protected function _getMainRoutingPath();
        abstract protected function getSpringMiddleware();
        abstract protected function ajaxCall($options = []);
        abstract protected function ajaxCallPost($options = []);
        abstract protected function ajaxCallDelete($options = []);
        abstract protected function createPath($parameters);

        /* ------------- Spring - GET ----------------- */
        private function springGet()
        {
                return $this->getSpringMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_GET, $this->_getMainRoutingPath() . $this->createPath($_REQUEST),
                    ['auth' => ['admin', 'adminPass']])
                ->sentRequest();
        }
        /* ------------------------------------------- */

        /* ------------- Spring - POST ----------------- */
        private function springPost()
        {
                return $this->getSpringMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_POST, $this->_getMainRoutingPath() . $this->createPath($_GET))
                ->sentRequest();
        }
        /* ------------------------------------------- */

        /* ------------- Spring - DELETE ----------------- */
        private function springDelete()
        {
                return  $this->getSpringMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_DELETE, $this->_getMainRoutingPath() . $this->createPath($_GET))
                ->sentRequest();
        }
        /* ------------------------------------------- */

        /* ------------- Spring -> Symfony - GET ----------------- */
        private function springSymfonyGet()
        {
            $this->ajaxCall(['springResponse' => $this->springGet()]);
        }
        /* ------------------------------------------- */

        /* ------------- Spring -> Symfony - POST ----------------- */
        private function springSymfonyPost()
        {
            $this->ajaxCallPost(['springResponse' => $this->springPost()]);

        }
        /* ------------------------------------------- */

        /* ------------- Spring -> Symfony - DELETE ----------------- */
        private function springSymfonyDelete()
        {
            $this->ajaxCallDelete(['springResponse' => $this->springDelete()]);

        }
        /* ------------------------------------------- */
    }

