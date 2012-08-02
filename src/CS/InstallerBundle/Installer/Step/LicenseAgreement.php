<?php

namespace CS\InstallerBundle\Installer\Step;

use Symfony\Component\Finder\Finder;

use CS\InstallerBundle\Installer\Step;

class LicenseAgreement extends Step
{
    /**
     * The view to render for this installation step
     *
     * @var string $view;
     */
    public $view = 'CSInstallerBundle:Install:license_agreement.html.twig';

    /**
     * The title to display when this installationj step is active
     *
     * @var string $title
     */
    public $title = 'License Agreement';

    /**
     * The license agreement text
     *
     * @var string $license
     */
    public $license;

    /**
     * Validates that the user accepted the license agreement
     *
     * @param  array   $request
     * @return boolean
     */
    public function validate($request = array())
    {
        if (isset($request['accept']) && $request['accept'] === 'on') {
            return true;
        }

        $this->addError('Please accept the license agreement');

        return false;
    }

    /**
     * Not implemented
     */
    public function process($request = array()){}

    /**
     * Reads through all the files in the root directory to find the license file so it can be shown to the user
     *
     * @return void
     */
    public function start()
    {
        $root_dir = dirname($this->get('kernel')->getRootDir());

        $finder = new Finder();
        $finder->files()->in($root_dir)->depth('== 0')->filter(function(\SplFileInfo $file){
                if ($file->getExtension() !== '') {
                    return false;
                }
            });

        foreach ($finder as $file) {
            if (strtolower($file->getBasename()) === 'license') {
                $this->license = $file->getContents();
                break;
            }
        }
    }
}
