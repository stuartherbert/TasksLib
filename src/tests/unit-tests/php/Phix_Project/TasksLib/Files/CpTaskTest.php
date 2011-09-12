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

class Files_CpTaskTest extends \PHPUnit_Framework_TestCase
{
        public function testCanInstantiate()
        {
                $task = new Files_CpTask();
                $this->assertTrue($task instanceof Files_CpTask);
                $this->assertTrue($task instanceof TaskBase);
        }
        
        public function testCanInitialise()
        {
                $task = new Files_CpTask();
                $task->initWithFilesOrFolders('/tmp/cptasktest', '/tmp/cptasktestdest');
                $task->requireInitialisedTask();
                
                // if we get here, the previous method call did not throw
                // an exception
                $this->assertTrue(true);
        }
        
        public function testThrowsExceptionIfNotInitialised()
        {
                // setup
                $queue = new TaskQueue();
                $task = new Files_CpTask();
                $queue->queueTask($task);
                
                // action
                $caughtException = false;
                try
                {
                        $queue->executeTasks();
                }
                catch (E5xx_TaskNotInitialisedException $e)
                {
                        $caughtException = true;
                }
                
                // check
                $this->assertTrue($caughtException);
        }
        
        public function testCanCopyFilesToNewFilename()
        {
                // setup
                $fileToCopy = "/tmp/cptasktest";
                if (!file_exists($fileToCopy))
                {
                        file_put_contents($fileToCopy, '');
                }
                $fileToCopyTo = '/tmp/cptasktest2';
                if (file_exists($fileToCopyTo))
                {
                        unlink($fileToCopyTo);
                }
                
                $queue = new TaskQueue();
                $task  = new Files_CpTask();                
                $task->initWithFilesOrFolders($fileToCopy, $fileToCopyTo);
                $queue->queueTask($task);
                
                $this->assertTrue(file_exists($fileToCopy));
                $this->assertFalse(file_exists($fileToCopyTo));
                
                // action
                $queue->executeTasks();
                
                // check
                $this->assertTrue(file_exists($fileToCopyTo));
                
                // clean up after ourselves
                unlink($fileToCopy);
                unlink($fileToCopyTo);
        }

        public function testCanCopyFilesIntoFolder()
        {
                // setup
                $fileToCopy = "/tmp/cptasktest";
                if (!file_exists($fileToCopy))
                {
                        file_put_contents($fileToCopy, '');
                }
                
                $dirToCopyTo = '/tmp/cptasktestdir';
                if (!is_dir($dirToCopyTo))
                {
                        mkdir($dirToCopyTo);
                }
                
                $fileToCopyTo = $dirToCopyTo . '/cptasktest';
                if (file_exists($fileToCopyTo))
                {
                        unlink($fileToCopyTo);
                }
                
                $queue = new TaskQueue();
                $task  = new Files_CpTask();                
                $task->initWithFilesOrFolders($fileToCopy, $dirToCopyTo);
                $queue->queueTask($task);
                
                $this->assertTrue(file_exists($fileToCopy));
                $this->assertFalse(file_exists($fileToCopyTo));
                
                // action
                $queue->executeTasks();
                
                // check
                $this->assertTrue(file_exists($fileToCopyTo));
                
                // clean up after ourselves
                unlink($fileToCopy);
                unlink($fileToCopyTo);
                rmdir($dirToCopyTo);
        }
        
        public function testCanCopyFolders()
        {
                // setup
                $baseCopyFromDir = '/tmp/cptasktest-from';
                $this->createFolder($baseCopyFromDir);
                
                $baseCopyToDir = '/tmp/cptasktest-to';
                $this->createFolder($baseCopyToDir);
                
                $files = array (
                    '1.txt',
                    '1/2.txt',
                    '2/3.txt',
                    '3/4.txt',
                    '3/4/5.txt'
                );
                
                foreach ($files as $filename)
                {
                        $this->createFile($baseCopyFromDir . '/' . $filename);
                        $this->assertTrue(!file_exists($baseCopyToDir . '/' . $filename));
                }
                
                $queue = new TaskQueue();
                $task  = new Files_CpTask();                
                $task->initWithFilesOrFolders($baseCopyFromDir, $baseCopyToDir);
                $queue->queueTask($task);
                
                // action
                $queue->executeTasks();
                
                // check
                foreach ($files as $filename)
                {
                        $this->assertTrue(file_exists($baseCopyToDir . '/' . $filename));
                        unlink($baseCopyFromDir . '/' . $filename);
                        unlink($baseCopyToDir . '/' . $filename);
                }
                
                // clean up after ourselves
                foreach (array_reverse($files) as $filename)
                {
                        $dir = dirname($baseCopyFromDir . '/' . $filename);
                        rmdir($dir);
                        
                        $dir = dirname($baseCopyToDir . '/' . $filename);
                        rmdir($dir);
                }
        }
        
        // helper functions
        
        protected function createFile($filename)
        {
                $now = time();
                
                if (!is_dir(dirname($filename)))
                {
                        $this->createFolder(dirname($filename));
                }
                file_put_contents($filename, $now);
                
                return $now;
        }
        
        protected function createFolder($folder)
        {
                $parts = explode('/', $folder);
                
                $folderToMake = '';
                foreach ($parts as $part)
                {
                        $folderToMake .= '/' . $part;
                        if (!is_dir($folderToMake))
                        {
                                mkdir($folderToMake);
                        }
                }
        }
}