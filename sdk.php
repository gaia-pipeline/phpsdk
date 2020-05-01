<?php

declare(strict_types = 1);

require dirname(__FILE__).'/vendor/autoload.php';
@include_once dirname(__FILE__).'/GPBMetadata/Plugin.php';
@include_once dirname(__FILE__).'/Proto/Argument.php';
@include_once dirname(__FILE__).'/Proto/Job.php';
@include_once dirname(__FILE__).'/Proto/JobResult.php';
@include_once dirname(__FILE__).'/Proto/ManualInteraction.php';
@include_once dirname(__FILE__).'/Proto/PBEmpty.php';
@include_once dirname(__FILE__).'/Proto/PluginInterface.php';

use Spiral\GRPC;

class ExitPipeline extends \Exception {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class PhpSDK {
    private $cachedJobs = [];
    private $certChainFilePath;
    private $privateKeyFilePath;
    private $trustCertCollectionFilePath;

    public function __construct() {
        $this->certChainFilePath = getenv("GAIA_PLUGIN_CERT");
        $this->privateKeyFilePath = getenv("GAIA_PLUGIN_KEY");
        $this->trustCertCollectionFilePath = getenv("GAIA_PLUGIN_CA_CERT");
    }

    public function Serve(array $jobs) {
        $jobMap = array_map(function ($j) {
            return strtolower($j->getTitle());
        }, $jobs);
        $uniqueIds = [];
        $this->cachedJobs = [];
        foreach($jobs as $job) {
            // Manual interaction
            $mBuilder = new ManualInteraction();
            if (!empty($job->getInteraction())) {
                $mBuilder->setDescription($job->getInteraction()->getDescription());
                $mBuilder->setType($job->getInteraction()->getType());
            }

            // Arguments
            $args = [];
            if (!empty($job->getArgs())) {
                foreach($job->getArgs() as $arg) {
                    $protoArg = new Argument();
                    $protoArg->setDescription($arg->getDescription());
                    $protoArg->setType($arg->getType()->getType());
                    $protoArg->setKey($arg->getKey());

                    $args[] = $protoArg;
                }
            }

            // Resolve dependencies
            $dependsOn = [];
            if (!empty($job->getDependsOn())) {
                foreach($job->getDependsOn() as $depend) {
                    $key = strtolower($depend->getTitle());
                    if (array_key_exists($key, $jobMap)) {
                        $dependsOn[] = crc32($jobMap[$key]->getTitle());
                    } else {
                        throw new ExitPipeline("job {$job->getTitle()} has dependency {$depend->getTitle()} which is not declared!")
                    }
                }
            }

            // Create gRPC plugin object
            $grpcJob = new Job();
            $grpcJob->setUniqueId(crc32($job->getTitle()));
            $grpcJob->setTitle($job->getTitle());
            $grpcJob->setDescription($job->getDescription());
            $grpcJob->setInteraction($mBuilder);
            $grpcJob->setArgs($args);
            $grpcJob->setDependson($dependsOn);

            $uniqueIds[$grpcJob->getUniqueId()]++;
            if $uniqueIds[$grpcJob->getUniqueId()] > 1 {
                throw new ExitPipeline("duplicate job: {$job->getTitle()} was already defined. This is not allowed!")
            }

            $wrapper = new JobWrapper($job->getHandler(), $grpcJob);
            $this->cachedJobs[] = $wrapper;
        }

        
    }
}

class PipelineJob {
    private $handler;
    private $title;
    private $description;
    private $dependsOn;
    private $args;
    private $manualInteraction;

    public function __construct($handler, $title, $description, $dependsOn = null, $args = null, $manualInteraction = null) {
        $this->handler = $handler;
        $this->title = $title;
        $this->description = $description;
        $this->dependsOn = $dependsOn;
        $this->args = $args;
        $this->manualInteraction = $manualInteraction;
    }

    public function getHandler() {
        return $this->handler;
    }

    public function setHandler($handler) {
        $this->handler = $handler;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getDescription() {
        return description;
    }

    public function setDescription(String description) {
        this.description = description;
    }

    public function getDependsOn() {
        return dependsOn;
    }

    public function setDependsOn(ArrayList<String> dependsOn) {
        this.dependsOn = dependsOn;
    }

    public function getArgs() {
        return args;
    }

    public function setArgs(ArrayList<PipelineArgument> args) {
        this.args = args;
    }

    public function getInteraction() {
        return interaction;
    }

    public function setInteraction(PipelineManualInteraction interaction) {
        this.interaction = interaction;
    }
}

/*
 * Wrapper for the job to send back.
 */
class JobWrapper {
    // handler function
    private $handler;
    // proto job
    private $job;

    public function __construct($handler, $job) {
        $this->handler = $handler;
        $this->job = $job;
    }

    public function setHandler(callback $handler) {
        $this->handler = $handler;
    }

    public function setJob($job) {
        $this->job = $job;
    }

    public function getHandler() {
        return $this->handler;
    }

    public function getJob() {
        return $this->job;
    }
}

/*
 * Implementation of the Plugin interface.
 */
class GRPCServer implements PluginInterface {
    private $cachedJobs = [];

    public function __construct(array $cachedJobs) {
        $this->cachedJobs = $cachedJobs;
    }

    public function GetJobs(GRPC\ContextInterface $ctx, Empty $in): Job {
        return null;
    }
    public function ExecuteJob(GRPC\ContextInterface $ctx, Job $in): JobResult {
        return null;
    }
}