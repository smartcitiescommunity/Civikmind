<?php

namespace tests\units;

use atoum;

class PluginActualtimeTask extends atoum {

   public function testRightname() {
      $this
         ->given($conf = $this->getTestedClassName())
            ->string($conf::$rightname)
               ->isEqualTo('task');
   }

   public function testGetTypeName() {
      $this
         ->if($class = $this->testedClass->getClass())
         ->then
            ->string($class::getTypeName())
               ->isNotEmpty();
   }

   /*
    * Actually is not easy to test the class, as it depends on already created
    * ticket with already created task to start timer. In future we should
    * probably emulate those tests. Now, just testing some results methods
    * should return if there is no tasks at all.
    */

   public function testCheckTech() {
      $this
         ->if($class = $this->testedClass->getClass())
         ->then
            ->boolean($class::checkTech(1))
               ->isFalse();
   }

   public function testCheckTimerActive() {
      $this
         ->if($class = $this->testedClass->getClass())
         ->then
            ->boolean($class::checkTimerActive(1))
               ->isFalse();
   }

   public function testTotalEndTime() {
      $this
         ->if($class = $this->testedClass->getClass())
         ->then
            ->integer($class::totalEndTime(1))
               ->isIdenticalTo(0);
   }
}
