<?php

interface ControllerInterface {
  public function index();
  public function retrieve();
  public function create();
  public function update();
  public function delete();
}
