<?php

namespace tests\units;

use atoum;

class PluginActualtimeConfig extends atoum {

   public function testRightname() {
      $this
         ->given($conf = $this->getTestedClassName())
            ->string($conf::$rightname)
               ->isEqualTo('config');
   }

   public function testGetTypeName() {
      $this
         ->if($class = $this->testedClass->getClass())
         ->then
            ->string($class::getTypeName())
               ->isNotEmpty();
   }

   public function testGetConfig() {
      $this
         ->given($this->newTestedInstance)
            ->object($this->testedInstance->getConfig())
               ->isInstanceOfTestedClass();
   }

   public function testShowTimerPopup() {
      $this
         ->given($this->newTestedInstance)
            ->boolean($this->testedInstance->showTimerPopup())
               ->isTrue();
   }

   public function testShowInHelpdesk() {
      $this
         ->given($this->newTestedInstance)
            ->boolean($this->testedInstance->showInHelpdesk())
               ->isFalse();
   }

   public function testShowTimerInBox() {
      $this
         ->given($this->newTestedInstance)
            ->boolean($this->testedInstance->showTimerInBox())
               ->isTrue();
   }

   public function testAutoOpenNew() {
      $this
         ->given($this->newTestedInstance)
            ->boolean($this->testedInstance->autoOpenNew())
               ->isFalse();
   }

   public function testAutoOpenRunning() {
      $this
         ->given($this->newTestedInstance)
            ->boolean($this->testedInstance->autoOpenRunning())
               ->isFalse();
   }

   public function testCanView() {
      $this
         ->given($this->newTestedInstance)
            ->boolean($this->testedInstance->canView())
               ->isFalse();
   }

   public function testCanCreate() {
      $this
         ->given($this->newTestedInstance)
            ->boolean($this->testedInstance->canCreate())
               ->isFalse();
   }

}
