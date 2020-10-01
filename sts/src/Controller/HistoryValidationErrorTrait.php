<?php

namespace App\Controller;

trait HistoryValidationErrorTrait
{
    abstract protected function  getManager();

    /**
     * Remove broken historical entry
     *
     */
    private function resetBrokenHistory() {
        if (isset($_SESSION['tmp_history']['historical_table'])) {
            $manager = $this->getManager();
            $historicalEntry = $manager->getRepository($_SESSION['tmp_history']['historical_table'])
                ->find($_SESSION['tmp_history']['id']);

            $manager->remove($historicalEntry);
            $manager->flush();

            unset($_SESSION['tmp_history']);
        }
    }
}