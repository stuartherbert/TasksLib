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

class Files_ChmodTaskTest extends PHPUnit_Framework_TestCase
{
        public function testCanInstantiate()
        {
                $task = new Files_ChmodTask();
                $this->assertTrue($task instanceof Files_ChmodTask);
                $this->assertTrue($task instanceof TaskBase);
        }

        public function testCanInitialise()
        {
                $task = new Files_ChmodTask();
                $task->initWithFileAndMode('/tmp/chmodtasktest', 0644);
                $task->requireInitialisedTask();

                // if we get here, the previous method call did not throw
                // an exception
                $this->assertTrue(true);
        }

        public function testThrowsExceptionIfNotInitialised()
        {
                // setup
                $queue = new TaskQueue();
                $task = new Files_ChmodTask();

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

        public function testCanSetModeOnFiles()
        {
                // setup
                $fileToChange = "/tmp/chmodtasktest";
                $targetMode   = 0755;
                if (!file_exists($fileToChange))
                {
                        file_put_contents($fileToChange, '');
                }
                chmod($fileToChange, 0644);

                $queue = new TaskQueue();
                $task  = new Files_ChmodTask();
                $task->initWithFileAndMode($fileToChange, $targetMode);
                $queue->queueTask($task);

                $this->assertTrue(file_exists($fileToChange));

                // action
                $queue->executeTasks();

                // check
                $this->assertEquals(0100755, fileperms($fileToChange));

                // clean up after ourselves
                \unlink($fileToChange);
        }
}