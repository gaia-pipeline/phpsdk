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

$cached_pipeline = [];

/*
 * Implementation of the Plugin interface.
 */
class GRPCServer implements PluginInterface {
    public function GetJobs(GRPC\ContextInterface $ctx, Empty $in): Job {
        return null;
    }
    public function ExecuteJob(GRPC\ContextInterface $ctx, Job $in): JobResult {
        return null;
    }
}