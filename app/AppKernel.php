<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|BundleInterface An iterable of bundle instances
     */
    public function registerBundles()
    {
        return [];
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader
     *
     * @return array
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        return [];
    }
}
