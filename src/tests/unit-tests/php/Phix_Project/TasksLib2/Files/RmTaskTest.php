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
 * @copyright   2011-present Stuart Herbert
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\TasksLib2;

use PHPUnit_Framework_TestCase;

class Files_RmTaskTest extends PHPUnit_Framework_TestCase
{
        public function testCanInstantiate()
        {
                $task = new Files_RmTask();
                $this->assertTrue($task instanceof Files_RmTask);
                $this->assertTrue($task instanceof TaskBase);
        }

        public function testCanInitialise()
        {
                $task = new Files_RmTask();
                $task->initWithFolder('/tmp/mkdirtasktest');
                $task->requireInitialisedTask();

                // if we get here, the previous method call did not throw
                // an exception
                $this->assertTrue(true);
        }

        public function testThrowsExceptionIfNotInitialised()
        {
                // setup
                $queue = new TaskQueue();
                $task = new Files_RmTask();

                // action
                $caughtException = false;
                try
                {
                        $queue->queueTask($task);
                        $queue->executeTasks();
                }
                catch (E5xx_TaskNotInitialisedException $e)
                {
                        $caughtException = true;
                }

                // check
                $this->assertTrue($caughtException);
        }

        public function testCanDeleteFiles()
        {
                // setup
                $fileToRemove = "/tmp/rmktasktest";
                if (!file_exists($fileToRemove))
                {
                        file_put_contents($fileToRemove, '');
                }

                $queue = new TaskQueue();
                $task  = new Files_RmTask();
                $task->initWithFile($fileToRemove);
                $queue->queueTask($task);

                $this->assertTrue(file_exists($fileToRemove));

                // action
                $queue->executeTasks();

                // check
                $this->assertFalse(file_exists($fileToRemove));
        }

        public function testCanDeleteEmptyFolders()
        {
                // setup
                $folderToRemove = "/tmp/rmktasktest";
                if (!is_dir($folderToRemove))
                {
                        mkdir($folderToRemove);
                }

                $queue = new TaskQueue();
                $task  = new Files_RmTask();
                $task->initWithFile($folderToRemove);
                $queue->queueTask($task);

                $this->assertTrue(is_dir($folderToRemove));

                // action
                $queue->executeTasks();

                // check
                $this->assertFalse(is_dir($folderToRemove));
        }

        public function testCanDeleteNestedFolders()
        {
                // setup
                $foldersToRemove = array(
                    '/tmp/rmtasktest',
                    '/tmp/rmtasktest/1',
                    '/tmp/rmtasktest/1/2'
                );

                foreach ($foldersToRemove as $folderToRemove)
                {
                        if (!is_dir($folderToRemove))
                        {
                                mkdir($folderToRemove);
                        }

                        $this->assertTrue(is_dir($folderToRemove));
                }

                $queue = new TaskQueue();
                $task  = new Files_RmTask();
                $task->initWithFile($foldersToRemove[0]);
                $queue->queueTask($task);

                // action
                $queue->executeTasks();

                // check
                $this->assertFalse(is_dir($foldersToRemove[0]));
        }

        public function testCanDeleteFoldersAndTheirContents()
        {
                // setup
                $foldersToRemove = array (
                    '/tmp/rmtasktest',
                    '/tmp/rmtasktest/1',
                    '/tmp/rmtasktest/1/2'
                );

                $filesToRemove = array (
                    '/tmp/rmtasktest/dummy.txt',
                    '/tmp/rmtasktest/1/dummy.txt',
                    '/tmp/rmtasktest/1/2/dummy.txt'
                );

                foreach ($foldersToRemove as $folderToRemove)
                {
                        if (!is_dir($folderToRemove))
                        {
                                mkdir($folderToRemove);
                        }

                        $this->assertTrue(is_dir($folderToRemove));
                }

                foreach ($filesToRemove as $fileToRemove)
                {
                        if (!file_exists($fileToRemove))
                        {
                                file_put_contents($fileToRemove, '');
                        }
                }

                $queue = new TaskQueue();
                $task  = new Files_RmTask();
                $task->initWithFile($foldersToRemove[0]);
                $queue->queueTask($task);

                // action
                $queue->executeTasks();

                // check
                $this->assertFalse(is_dir($foldersToRemove[0]));

                foreach ($filesToRemove as $fileToRemove)
                {
                        $this->assertFalse(file_exists($fileToRemove));
                }
        }
}