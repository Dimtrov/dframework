<?php
/*
 * RabbitORM Entity Annotation
 * author: Fabio Covolo Mazzo
 * https://github.com/fabiocmazzo/rabbit-orm
 */
namespace dFramework\components\orm\Annotations;
/**
 * @Annotation
 * @Target("CLASS")
 */
final class Entity implements Annotation
{
    /**
     * @var string
     */
    public $repositoryClass;
    /**
     * @var boolean
     */
    public $readOnly = false;
}