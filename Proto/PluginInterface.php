<?php
# Generated by the protocol buffer compiler (spiral/php-grpc). DO NOT EDIT!
# source: plugin.proto

namespace Proto;

use Spiral\GRPC;

interface PluginInterface extends GRPC\ServiceInterface
{
    // GRPC specific service name.
    public const NAME = "proto.Plugin";

    /**
    * @param GRPC\ContextInterface $ctx
    * @param Empty $in
    * @return Job
    *
    * @throws GRPC\Exception\InvokeException
    */
    public function GetJobs(GRPC\ContextInterface $ctx, Empty $in): Job;

    /**
    * @param GRPC\ContextInterface $ctx
    * @param Job $in
    * @return JobResult
    *
    * @throws GRPC\Exception\InvokeException
    */
    public function ExecuteJob(GRPC\ContextInterface $ctx, Job $in): JobResult;
}