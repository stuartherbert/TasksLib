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

class Files_RenameIfNotModifiedTask extends TaskBase
{
        protected $oldName   = null;
        protected $newName   = null;
        protected $oldMd5sum = null;
        
        public function initWithFileAndChecksum($oldName, $newName, $md5sum)
        {
                $this->oldName = $oldName;
                $this->newName = $newName;
                $this->md5sum  = $md5sum;
        }
        
        public function requireInitialisedTask()
        {
                if ($this->oldName == null || $this->newName == null || $this->oldMd5sum == null)
                {
                        throw new E5xx_TaskNotInitialisedException(__CLASS__);
                }
        }
        
        protected function performTask()
        {
                $actualSum = md5_file($this->oldName);
                if ($actualSum == $this->oldMd5sum)
                {
                        $this->moveFile($this->oldName, $this->newName);
                }
        }
        
        protected function moveFile($src, $dest)
        {
                // make sure $dest is a filename
                if (is_dir($dest))
                {
                        $dest = $dest . DIRECTORY_SEPARATOR . basename($src);
                }
                
                // move the file
                rename($src, $dest);
        }
        
        public function requireSuccessfulTask()
        {
                // does the destination exist?
                if (!file_exists($this->newName))
                {
                        throw new E5xx_TaskFailedException(__CLASS__, "original file had been modified");
                }
        }
}