<?php
class ZoomApi {
    private $productId;
    private $skuId;
    private $customerId;
    private $apiEndpoint;
    private $licenseCap;

    public function __construct($config) {
        $this->productId = $config['productId'];
        $this->skuId = $config['skuId'];
        $this->customerId = $config['customerId'];
        $this->apiEndpoint = $config['apiEndpoint'];
        $this->licenseCap = $config['licenseCap'];
    }

    public function connect() {
        // Code to connect to Google API using $this->apiEndpoint
        echo "Connected to Google API at " . $this->apiEndpoint;
    }

    public function getLicenseCount() {
        // Code to fetch license count from Google API
        // This is a placeholder return value
        return 500;
    }

    public function getLicenseCap() {
        return $this->licenseCap;
    }
}