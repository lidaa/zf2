<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_PDF
 * @package    Zend_PDF_Internal
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\PDF\InternalType;
use Zend\PDF\ObjectFactory;
use Zend\PDF;

/**
 * PDF file 'indirect object' element implementation
 *
 * @uses       \Zend\PDF\InternalType\AbstractTypeObject
 * @uses       \Zend\PDF\ObjectFactory\ObjectFactory
 * @uses       \Zend\PDF\Exception
 * @category   Zend
 * @package    Zend_PDF
 * @package    Zend_PDF_Internal
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IndirectObject extends AbstractTypeObject
{
    /**
     * Object value
     *
     * @var \Zend\PDF\InternalType\AbstractTypeObject
     */
    protected $_value;

    /**
     * Object number within PDF file
     *
     * @var integer
     */
    protected $_objNum;

    /**
     * Generation number
     *
     * @var integer
     */
    protected $_genNum;

    /**
     * Reference to the factory.
     *
     * @var \Zend\PDF\ObjectFactory\ObjectFactory
     */
    protected $_factory;

    /**
     * Object constructor
     *
     * @param \Zend\PDF\InternalType\AbstractTypeObject $val
     * @param integer $objNum
     * @param integer $genNum
     * @param \Zend\PDF\ObjectFactory\ObjectFactory $factory
     * @throws \Zend\PDF\Exception
     */
    public function __construct(AbstractTypeObject $val, $objNum, $genNum, ObjectFactory\ObjectFactory $factory)
    {
        if ($val instanceof self) {
            throw new PDF\Exception('Object number must not be an instance of \Zend\PDF\InternalType\IndirectObject.');
        }

        if ( !(is_integer($objNum) && $objNum > 0) ) {
            throw new PDF\Exception('Object number must be positive integer.');
        }

        if ( !(is_integer($genNum) && $genNum >= 0) ) {
            throw new PDF\Exception('Generation number must be non-negative integer.');
        }

        $this->_value   = $val;
        $this->_objNum  = $objNum;
        $this->_genNum  = $genNum;
        $this->_factory = $factory;

        $this->setParentObject($this);

        $factory->registerObject($this, $objNum . ' ' . $genNum);
    }


    /**
     * Check, that object is generated by specified factory
     *
     * @return \Zend\PDF\ObjectFactory\ObjectFactory
     */
    public function getFactory()
    {
        return $this->_factory;
    }

    /**
     * Return type of the element.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->_value->getType();
    }


    /**
     * Get object number
     *
     * @return integer
     */
    public function getObjNum()
    {
        return $this->_objNum;
    }


    /**
     * Get generation number
     *
     * @return integer
     */
    public function getGenNum()
    {
        return $this->_genNum;
    }


    /**
     * Return reference to the object
     *
     * @param Zend_PDF_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        if ($factory === null) {
            $shift = 0;
        } else {
            $shift = $factory->getEnumerationShift($this->_factory);
        }

        return $this->_objNum + $shift . ' ' . $this->_genNum . ' R';
    }


    /**
     * Dump object to a string to save within PDF file.
     *
     * $factory parameter defines operation context.
     *
     * @param \Zend\PDF\ObjectFactory\ObjectFactory $factory
     * @return string
     */
    public function dump(ObjectFactory\ObjectFactory $factory)
    {
        $shift = $factory->getEnumerationShift($this->_factory);

        return  $this->_objNum + $shift . " " . $this->_genNum . " obj \n"
             .  $this->_value->toString($factory) . "\n"
             . "endobj\n";
    }

    /**
     * Get handler
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->_value->$property;
    }

    /**
     * Set handler
     *
     * @param string $property
     * @param  mixed $value
     */
    public function __set($property, $value)
    {
        $this->_value->$property = $value;
    }

    /**
     * Call handler
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_value, $method), $args);
    }


    /**
     * Mark object as modified, to include it into new PDF file segment
     */
    public function touch()
    {
        $this->_factory->markAsModified($this);
    }

    /**
     * Return object, which can be used to identify object and its references identity
     *
     * @return \Zend\PDF\InternalType\IndirectObject
     */
    public function getObject()
    {
        return $this;
    }

    /**
     * Clean up resources, used by object
     */
    public function cleanUp()
    {
        $this->_value = null;
    }

    /**
     * Convert PDF element to PHP type.
     *
     * @return mixed
     */
    public function toPhp()
    {
        return $this->_value->toPhp();
    }
}