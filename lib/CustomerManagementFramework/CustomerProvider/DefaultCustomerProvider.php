<?php

namespace CustomerManagementFramework\CustomerProvider;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\Folder;

class DefaultCustomerProvider implements CustomerProviderInterface
{
    /**
     * @var string
     */
    protected $pimcoreClass;

    /**
     * @var string
     */
    protected $parentPath;

    /**
     * @var Folder
     */
    protected $parentFolder;

    public function __construct()
    {
        $this->pimcoreClass = Plugin::getConfig()->General->CustomerPimcoreClass;
        if (empty($this->pimcoreClass)) {
            throw new \RuntimeException('Customer class is not defined');
        }

        $config = Plugin::getConfig()->CustomerProvider;
        $this->parentPath = $config->parentPath;

        if (empty($this->parentPath)) {
            throw new \RuntimeException('Customer save path is not defined');
        }
    }

    /**
     * @return string
     */
    protected function getDiClassName()
    {
        return sprintf('Pimcore\Model\Object\%s', $this->pimcoreClass);
    }

    /**
     * @return string
     */
    protected function getDiListingClassName()
    {
        return sprintf('Pimcore\Model\Object\%s\Listing', $this->pimcoreClass);
    }

    /**
     * @return int
     */
    public function getCustomerClassId()
    {
        return $this->callStatic('classId');
    }

    /**
     * @return string
     */
    public function getCustomerClassName()
    {
        return get_class(\Pimcore::getDiContainer()->make($this->getDiClassName()));
    }

    /**
     * Get an object listing
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getList()
    {
        return \Pimcore::getDiContainer()->make($this->getDiListingClassName());
    }

    /**
     * Create a customer instance
     *
     * @param array $values
     * @return CustomerInterface
     */
    public function create(array $values = [])
    {
        $parentFolder = Folder::getByPath($this->parentPath);
        if (!$parentFolder) {
            throw new \RuntimeException(sprintf('Parent folder %s could not be loaded', $parentFolder));
        }

        /** @var CustomerInterface|ElementInterface|Concrete $customer */
        $customer = \Pimcore::getDiContainer()->make($this->getDiClassName());
        $customer->setPublished(true);
        $customer->setParent($parentFolder);
        $customer->setValues($values);

        return $customer;
    }

    /**
     * Get customer by ID
     *
     * @param int $id
     * @return CustomerInterface|null
     */
    public function getById($id)
    {
        return $this->callStatic('getById', [$id]);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    protected function callStatic($method, array $arguments = [])
    {
        $className = $this->getDiClassName();

        return call_user_func_array([$className, $method], $arguments);
    }
}