<?php

/**
 * Copyright (c) 2011 Stuart Herbert.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Phix_Project
 * @subpackage  TasksLib
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2011 Stuart Herbert
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\TasksLib;

use Phix_Project\ExceptionsLib\Legacy_ErrorHandler;

class Files_RmdirTask extends TaskBase
{
        /**
         * The folder that we want to remove
         * 
         * @var string - path to folder to remove
         */
        protected $targetFolder = null;
        
        public function initWithFolder($folder)
        {
                $target = \realpath($folder);
                
                return $this;
        }
        
        protected function requireInitialisedTask()
        {
                if ($this->targetFolder == null)
                {
                        throw new E5xx_TaskNotInitialisedException(__CLASS__);
                }
        }
        
        protected function performTask()
        {
                // are we removing a file or a folder?
                if (!is_dir($this->targetFolder))
                {
                        // we think we are removing a file
                        \unlink($this->targetFolder);
                }
                else
                {
                        // we think we are removing a folder
                        $handler = new Legacy_ErrorHandler();
                        $this->recursiveRmdir($this->targetFolder, $handler);
                }
        }
        
        protected function recursiveRmdir($folder, Legacy_ErrorHandler $handler)
        {
                $wrapped = function($folder) {
                        \opendir($folder);
                };
                
                $dir = $handler->run($folder);
                if (!$dir)
                {
                        throw new E5xx_TaskFailedException(__CLASS__, "unable to open folder " . $folder . ' for reading');
                }

                while (false !== ($entry = \readdir($dir)))
                {
                        if ($entry == '.' || $entry == '..')
                        {
                                continue;
                        }

                        $fqFile = $folder . DIRECTORY_SEPARATOR . $entry;
                        if (\is_dir($fqFile))
                        {
                                $this->recursiveRmdir($fqFile);
                        }
                        else
                        {
                                \unlink($fqFile);
                        }
                }

                \closedir($dir);

                \rmdir($folder);                
        }
        
        protected function requireSuccessfulTask()
        {
                // does the folder still exist?
                if (file_exists($this->targetFolder))
                {
                        throw new E5xx_TaskFailedException(__CLASS__, $this->targetFolder . " exists after removal attempt");
                }
        }
}