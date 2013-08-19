<?php

namespace Objects\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Yaml\Yaml;

/**
 * Admin controller.
 */
class AdminController extends Controller {

    private function clearProductionCache() {
        exec(PHP_BINDIR . '/php-cli ' . __DIR__ . '/../../../../app/console cache:clear -e prod');
        exec(PHP_BINDIR . '/php-cli ' . __DIR__ . '/../../../../app/console cache:warmup --no-debug -e prod');
    }

    public function siteConfigurationsAction() {
        $configFilePath = __DIR__ . '/../../SiteBundle/Resources/config/config.yml';
        $parsedData = Yaml::parse(file_get_contents($configFilePath));
        $formBuilder = $this->createFormBuilder($parsedData['parameters']);
        $parameters = array_keys($parsedData['parameters']);
        foreach ($parameters as $parameter) {
            $formBuilder->add($parameter, 'text', array('constraints' => new Constraints\NotBlank()));
        }
        $form = $formBuilder->getForm();
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                file_put_contents($configFilePath, Yaml::dump(array('parameters' => $form->getData()), 3));
                $request->getSession()->getFlashBag()->add('success', 'Saved Successfully');
                $this->clearProductionCache();
            }
        }
        return $this->render('ObjectsAdminBundle:Admin:siteConfigurations.html.twig', array(
                    'parameters' => $parameters,
                    'form' => $form->createView()
        ));
    }

    public function siteEmailsAction() {
        $contactUsFilePath = __DIR__ . '/../../../../app/Resources/views/Emails/contact_us.txt';
        $initialData = array();
        $initialData['contactUsText'] = file_get_contents($contactUsFilePath);
        $form = $this->createFormBuilder($initialData)
                ->add('contactUsText', 'textarea', array('constraints' => new Constraints\NotBlank(), 'required' => false, 'attr' => array('class' => 'ckeditor')))
                ->getForm();
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                file_put_contents($contactUsFilePath, $data['contactUsText']);
                $request->getSession()->getFlashBag()->add('success', 'Saved Successfully');
                $this->clearProductionCache();
            }
        }
        return $this->render('ObjectsAdminBundle:Admin:siteEmails.html.twig', array(
                    'form' => $form->createView()
        ));
    }

}
