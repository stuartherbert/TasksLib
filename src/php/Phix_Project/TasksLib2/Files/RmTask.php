<?php

/**
 * Copyright (c) 2011-present Stuart Herbert.
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
 * @subpackage  TasksLib2
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2011 Stuart Herbert
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\TasksLib2;

use Phix_Project\ExceptionsLib\Legacy_ErrorHandler;

class Files_RmTask extends TaskBase
{
        /**
         * The folder that we want to remove
         *
         * @var string - path to folder or file to remove
         */
        protected $target = null;

        public function initWithFolder($folder)
        {
                $this->target = $folder;
                return $this;
        }

        public function initWithFolders($folders)
        {
                $this->target = $folders;
                return $this;
        }

        public function initWithFile($filename)
        {
                $this->target = $filename;
                return $this;
        }

        public function requireInitialisedTask()
        {
                if ($this->target == null)
                {
                        throw new E5xx_TaskNotInitialisedException(__CLASS__);
                }
        }

        protected function performTask()
        {
                if (is_array($this->target))
                {
                        foreach ($this->target as $target)
                        {
                                $this->removeTarget($target);
                        }
                }
                else
                {
                        $this->removeTarget($this->target);
                }
        }

        protected function removeTarget($target)
        {
                // does our target exist at all?
                if (!file_exists($target))
                {
                        // no it does not ...
                        return;
                }

                // are we removing a file or a folder?
                if (!is_dir($target))
                {
                        // we think we are removing a file
                        \unlink($target);
                }
                else
                {
                        // we think we are removing a folder
                        $this->recursiveRmdir($target);
                }
        }

        protected function recursiveRmdir($folder)
        {
                $dir = \opendir($folder);
                if (!$dir)
                {
                        // @codeCoverageIgnoreStart
                        throw new E5xx_TaskFailedException(__CLASS__, "unable to open folder " . $folder . ' for reading');
                        // @codeCoverageIgnoreEnd
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

        public function requireSuccessfulTask()
        {
                if (is_array($this->target))
                {
                        $failedList = null;
                        foreach ($this->target as $target)
                        {
                                if (file_exists($target))
                                {
                                        $failedList[] = $target;
                                }
                        }

                        if ($failedList !== null)
                        {
                                // @codeCoverageIgnoreStart
                                throw new E5xx_TaskFailedException(__CLASS__, 'files/folders ' . implode(',', $failedList) . " exist after removal attempt");
                                // @codeCoverageIgnoreEnd
                        }
                }
                else
                {
                        // does the folder still exist?

                        if (file_exists($this->target))
                        {
                                // @codeCoverageIgnoreStart
                                throw new E5xx_TaskFailedException(__CLASS__, $this->target . " exists after removal attempt");
                                // @codeCoverageIgnoreEnd
                        }
                }
        }
}