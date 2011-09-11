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


class DummyTask extends TaskBase
{
        protected $initialised = false;
        protected $taskWillSucceed = false;
        
        public $taskPerformed = false;
        
        public function init($taskWillSucceed)
        {
                $this->initialised = true;
                $this->taskWillSucceed = $taskWillSucceed;
        }
        
        public function requireInitialisedTask()
        {
                if (!$this->initialised)
                {
                        throw new E5xx_TaskNotInitialisedException(__CLASS__);
                }
        }
        
        protected function performTask()
        {
                // make a note that we have been called
                $this->taskPerformed = true;
        }
        
        public function requireSuccessfulTask()
        {
                // this exception should never get thrown in testing
                if (!$this->initialised)
                {
                        throw new E5xx_TaskFailedException(__CLASS__, 'was never initialised');
                }
                
                // this exception should never get thrown in testing
                // it is here to detect a problem in executing tasks
                // properly
                if (!$this->taskPerformed)
                {
                        throw new E5xx_TaskFailedException(__CLASS__, 'performTask() never called');
                }
                
                // we can, and will, control whether this exception is
                // thrown during testing
                if (!$this->taskWillSucceed)
                {
                        throw new E5xx_TaskFailedException(__CLASS__, "did not succeed");
                }
        }
}

class TaskQueueTest extends \PHPUnit_Framework_TestCase
{
        public function testCanInitialise()
        {
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
        }
        
        public function testStartsWithEmptyQueue()
        {
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
                                
                // is the queue empty?
                $this->assertEquals(0, $queue->count());
        }        
        
        public function testCanAddTaskToQueue()
        {
                // setup
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
                
                $task = new DummyTask();
                
                // action
                $queue->queueTask($task);
                
                // check
                $this->assertEquals(1, $queue->count());
                $queuedTask = $queue->dequeue();
                $this->assertSame($task, $queuedTask);
                
                // add a second task
                $task2 = new DummyTask();
                $queue->queueTask($task);
                $queue->enqueue($task2);
                
                // check
                $this->assertEquals(2, $queue->count());
                $queuedTask = $queue->dequeue();
                $queuedTask2 = $queue->dequeue();
                $this->assertSame($task, $queuedTask);
                $this->assertSame($task2, $queuedTask2);
        }
        
        public function testCannotAddNonTaskToQueue()
        {
                // setup
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
                
                $nonTask = new \stdClass();
                $caughtException = false;
                
                // action
                try 
                {
                        $queue->enqueue($nonTask);
                }
                catch (E5xx_NotAValidTaskException $e)
                {
                        $caughtException = true;
                }
                
                $this->assertTrue($caughtException);
        }
        
        public function testCanExecuteTasks()
        {
                // setup
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
                
                $tasks = array
                (
                    new DummyTask(),
                    new DummyTask(),
                    new DummyTask()
                );
                
                foreach ($tasks as $task)
                {
                        $task->init(true);
                        $this->assertFalse($task->taskPerformed);
                        $queue->queueTask($task);
                }
                
                // action
                $queue->executeTasks();
                
                // make sure each task was executed
                foreach ($tasks as $task)
                {
                        $this->assertTrue($task->taskPerformed);
                }
        }
        
        public function testThrowsExceptionWhenExecutingUninitialisedTask()
        {
                // setup
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
                
                $tasks = array
                (
                    new DummyTask(),
                    new DummyTask(),
                    new DummyTask()
                );

                // initialise just the first one
                $tasks[0]->init(true);
                
                foreach ($tasks as $task)
                {
                        $this->assertFalse($task->taskPerformed);
                        $queue->queueTask($task);
                }
                
                $caughtException = false;
                
                // action
                try
                {                        
                        $queue->executeTasks();
                }
                catch (E5xx_TaskNotInitialisedException $e)
                {
                        $caughtException = true;
                }
                
                // check the results
                $this->assertTrue($caughtException);
                $this->assertTrue($tasks[0]->taskPerformed);
                $this->assertFalse($tasks[1]->taskPerformed);
                $this->assertFalse($tasks[2]->taskPerformed);                
        }
        
        public function testThrowsExceptionWhenATaskFails()
        {
                // setup
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
                
                $task = new DummyTask();
                $task->init(false);
                $queue->queueTask($task);
                
                $caughtException = false;
                
                // action
                try
                {                        
                        $queue->executeTasks();
                }
                catch (E5xx_TaskFailedException $e)
                {
                        $caughtException = true;
                }
                
                // check the results
                $this->assertTrue($caughtException);
                $this->assertTrue($task->taskPerformed);
        }
        
        public function testStopsExecutingTheQueueWhenATaskFails()
        {
                // setup
                $queue = new TaskQueue();
                $this->assertTrue($queue instanceof TaskQueue);
                
                $tasks = array
                (
                    new DummyTask(),
                    new DummyTask(),
                    new DummyTask()
                );

                // initialise just the first one
                $tasks[0]->init(true);
                $tasks[1]->init(false);
                
                foreach ($tasks as $task)
                {
                        $this->assertFalse($task->taskPerformed);
                        $queue->queueTask($task);
                }
                
                $caughtException = false;
                
                // action
                try
                {                        
                        $queue->executeTasks();
                }
                catch (E5xx_TaskFailedException $e)
                {
                        $caughtException = true;
                }
                
                // check the results
                $this->assertTrue($caughtException);
                
                // first task succeeded, no exception
                $this->assertTrue($tasks[0]->taskPerformed);
                
                // second task was performed, but failed
                $this->assertTrue($tasks[1]->taskPerformed);
                
                // third task was never executed
                $this->assertFalse($tasks[2]->taskPerformed);                
        }
}