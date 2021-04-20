<?php

/*
 -------------------------------------------------------------------------
 Web Resources Plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/glpi-webresources-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Web Resources Plugin for GLPI.
 Web Resources Plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Web Resources Plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Web Resources Plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use Glpi\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * @since 1.3.0
 */
final class PluginWebresourcesIconWarmupCommand extends AbstractCommand {

   protected function configure() {
      parent::configure();

      $this->setName('webresources:iconcache:warmup');
      $this->setDescription(__('Warmup the automatically generated icon cache. This is used for dynamic dashboards such as suppliers, appliances, and entities.'));
   }

   protected function execute(InputInterface $input, OutputInterface $output) {

      $start = round(microtime(true) * 1000);
      $contexts = PluginWebresourcesDashboard::getDashboardContexts(true);
      unset($contexts['personal']);
      $output->writeln('Warming up icon cache for: ' . implode(', ', $contexts));
      foreach ($contexts as $context => $name) {
         PluginWebresourcesDashboard::getDashboardContent($context, true, true);
      }
      $end = round(microtime(true) * 1000);
      $output->writeln('Done in ' . ($end - $start) . 'ms');

      return 0; // Success
   }
}