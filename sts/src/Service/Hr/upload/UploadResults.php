<?php

namespace App\Service\Hr\upload;

class UploadResults
{
  /**
   * @var array
   */
  public $errors;
  /**
   * @var array
   */
  public $success;
  /**
   * @var array
   */
  public $information;

  /**
   * UploadResults constructor.
   *
   * @param array         $errors
   * @param array         $success
   * @param array         $information
   */
  public function __construct(array $errors, array $success, array $information)
  {
    $this->errors= $errors;
    $this->success = $success;
    $this->information = $information;
  }
}