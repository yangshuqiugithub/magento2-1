<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var String
     */
    protected $fileName = 'magento.jpg';

    /**
     * @var String
     */
    protected $invalidFileName = '../../invalidFile.xyz';

    /**
     * @var String
     */
    protected $imageFixtureDir;

    /**
     * @var String
     */
    protected $expectedFileName;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageFixtureDir = realpath(__DIR__ . '/../../../_files/image');
        $this->expectedFileName = '/m/a/' . $this->fileName;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testProcessCustomerAddressValue()
    {
        $this->mediaDirectory->delete('customer_address');
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath('customer_address/tmp/'));
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath('customer_address/tmp/' . $this->fileName);
        copy($this->imageFixtureDir . DIRECTORY_SEPARATOR . $this->fileName, $tmpFilePath);

        $imageFile = [
            'name' => $this->fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $this->fileName,
            'file' => $this->fileName,
            'error' => 0,
            'size' => 12500,
            'previewType' => 'image',
        ];

        $params = [
            'entityTypeCode' => 'customer_address',
            'formCode' => 'customer_address_edit',
            'isAjax' => false,
            'value' => $imageFile
        ];

        $expectedPath = $this->mediaDirectory->getAbsolutePath('customer_address' . $this->expectedFileName);

        /** @var Image $image */
        $image = $this->objectManager->create(\Magento\Customer\Model\Metadata\Form\Image::class, $params);
        $processCustomerAddressValueMethod = new \ReflectionMethod(\Magento\Customer\Model\Metadata\Form\Image::class, 'processCustomerAddressValue');
        $processCustomerAddressValueMethod->setAccessible(true);
        $actual = $processCustomerAddressValueMethod->invoke($image, $imageFile);
        $this->assertEquals($this->expectedFileName, $actual);
        $this->assertFileExists($expectedPath);
        $this->assertFileNotExists($tmpFilePath);
    }

    /**
 * @magentoAppIsolation enabled
 */
    public function testProcessCustomerValue()
    {
        $this->mediaDirectory->delete('customer');
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath('customer/tmp/'));
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath('customer/tmp/' . $this->fileName);
        copy($this->imageFixtureDir . DIRECTORY_SEPARATOR . $this->fileName, $tmpFilePath);

        $imageFile = [
            'name' => $this->fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $this->fileName,
            'file' => $this->fileName,
            'error' => 0,
            'size' => 12500,
            'previewType' => 'image',
        ];

        $params = [
            'entityTypeCode' => 'customer',
            'formCode' => 'customer_edit',
            'isAjax' => false,
            'value' => $imageFile
        ];

        /** @var Image $image */
        $image = $this->objectManager->create(\Magento\Customer\Model\Metadata\Form\Image::class, $params);
        $processCustomerAddressValueMethod = new \ReflectionMethod(\Magento\Customer\Model\Metadata\Form\Image::class, 'processCustomerValue');
        $processCustomerAddressValueMethod->setAccessible(true);
        $result = $processCustomerAddressValueMethod->invoke($image, $imageFile);
        $this->assertInstanceOf('Magento\Framework\Api\ImageContent', $result);
        $this->assertFileNotExists($tmpFilePath);
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testProcessCustomerInvalidValue()
    {
        $this->mediaDirectory->delete('customer');
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath('customer/tmp/'));
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath('customer/tmp/' . $this->fileName);
        copy($this->imageFixtureDir . DIRECTORY_SEPARATOR . $this->fileName, $tmpFilePath);

        $imageFile = [
            'name' => $this->fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $this->fileName,
            'file' => $this->invalidFileName,
            'error' => 0,
            'size' => 12500,
            'previewType' => 'image',
        ];

        $params = [
            'entityTypeCode' => 'customer',
            'formCode' => 'customer_edit',
            'isAjax' => false,
            'value' => $imageFile
        ];

        /** @var Image $image */
        $image = $this->objectManager->create(\Magento\Customer\Model\Metadata\Form\Image::class, $params);
        $processCustomerAddressValueMethod = new \ReflectionMethod(\Magento\Customer\Model\Metadata\Form\Image::class, 'processCustomerValue');
        $processCustomerAddressValueMethod->setAccessible(true);
        $result = $processCustomerAddressValueMethod->invoke($image, $imageFile);
        $this->assertInstanceOf('array', $result);
        $this->assertFileExists($tmpFilePath);
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        );
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete('customer');
        $mediaDirectory->delete('customer_address');
    }
}
