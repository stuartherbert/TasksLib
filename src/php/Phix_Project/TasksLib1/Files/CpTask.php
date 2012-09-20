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
 * @subpackage  TasksLib1
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2011-present Stuart Herbert
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\TasksLib1;

class Files_CpTask extends TaskBase
{
        protected $src = null;
        protected $dest = null;

        public function initWithFilesOrFolders($src, $dest)
        {
                $this->src  = $src;
                $this->dest = $dest;
        }

        public function requireInitialisedTask()
        {
                if ($this->src == null || $this->dest == null)
                {
                        throw new E5xx_TaskNotInitialisedException(__CLASS__);
                }
        }

        protected function performTask()
        {
                if (!\is_dir($this->src))
                {
                        $this->copyFile($this->src, $this->dest);
                }
                else
                {
                        $this->recursiveCopyFolders($this->src, $this->dest);
                }
        }

        protected function copyFile($src, $dest)
        {
                // make sure $dest is a filename
                if (is_dir($dest))
                {
                        $dest = $dest . DIRECTORY_SEPARATOR . basename($src);
                }

                // copy the file
                copy($src, $dest);

                // set the mode to match
                chmod($dest, fileperms($src) & 0777);
        }

        protected function recursiveCopyFolders($src, $dest)
        {
                if (!\is_dir($dest))
                {
                        \mkdir($dest);
                }

                $dir = \opendir($src);
                if (!$dir)
                {
                        // @codeCoverageIgnoreStart
                        throw new \Exception('unable to open folder ' . $src . ' for reading');
                        // @codeCoverageIgnoreEnd
                }

                while (false !== ($entry = \readdir($dir)))
                {
                        if ($entry == '.' || $entry == '..')
                        {
                                continue;
                        }

                        $srcEntry = $src . DIRECTORY_SEPARATOR . $entry;
                        $dstEntry = $dest . DIRECTORY_SEPARATOR . $entry;

                        if (\is_file($srcEntry))
                        {
                                $this->copyFile($srcEntry, $dstEntry);
                        }
                        else if (\is_dir($srcEntry))
                        {
                                $this->recursiveCopyFolders($srcEntry, $dstEntry);
                        }
                }
                \closedir($dir);
        }

        public function requireSuccessfulTask()
        {
                // does the destination exist?

        }
}