<?php

namespace Proxies\__CG__\MOTO\PrincipalBundle\Entity;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class Tablaejercicios extends \MOTO\PrincipalBundle\Entity\Tablaejercicios implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    /** @private */
    public function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__to avoid infinite recursion
                // but before loading to emulate what ClassMetadata::newInstance()
                // provides.
                $this->__wakeup();
            }

            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    /** @private */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    
    public function getCodtabla()
    {
        if ($this->__isInitialized__ === false) {
            return (int) $this->_identifier["codtabla"];
        }
        $this->__load();
        return parent::getCodtabla();
    }

    public function setFecha($fecha)
    {
        $this->__load();
        return parent::setFecha($fecha);
    }

    public function getFecha()
    {
        $this->__load();
        return parent::getFecha();
    }

    public function setTipo($tipo)
    {
        $this->__load();
        return parent::setTipo($tipo);
    }

    public function getTipo()
    {
        $this->__load();
        return parent::getTipo();
    }

    public function addCodsesion(\MOTO\PrincipalBundle\Entity\Sesion $codsesion)
    {
        $this->__load();
        return parent::addCodsesion($codsesion);
    }

    public function removeCodsesion(\MOTO\PrincipalBundle\Entity\Sesion $codsesion)
    {
        $this->__load();
        return parent::removeCodsesion($codsesion);
    }

    public function getCodsesion()
    {
        $this->__load();
        return parent::getCodsesion();
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'codtabla', 'fecha', 'tipo', 'codsesion');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields as $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}