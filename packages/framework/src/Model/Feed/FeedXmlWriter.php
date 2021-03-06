<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Twig_Environment;
use Twig_TemplateWrapper;

class FeedXmlWriter
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $feedTemplatePath
     * @param string $targetFilepath
     */
    public function writeBegin(DomainConfig $domainConfig, $feedTemplatePath, $targetFilepath)
    {
        $twigTemplate = $this->twig->load($feedTemplatePath);

        $renderedBlock = $this->getRenderedBlock($twigTemplate, 'begin', ['domainConfig' => $domainConfig]);
        file_put_contents($targetFilepath, $renderedBlock);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $feedTemplatePath
     * @param string $targetFilepath
     */
    public function writeEnd(DomainConfig $domainConfig, $feedTemplatePath, $targetFilepath)
    {
        $twigTemplate = $this->twig->load($feedTemplatePath);
        $renderedBlock = $this->getRenderedBlock($twigTemplate, 'end', ['domainConfig' => $domainConfig]);
        file_put_contents($targetFilepath, $renderedBlock, FILE_APPEND);
    }

    /**
     * @param \Shopsys\ProductFeed\FeedItemInterface[] $items
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $feedTemplatePath
     * @param string $targetFilepath
     */
    public function writeItems(array $items, DomainConfig $domainConfig, $feedTemplatePath, $targetFilepath)
    {
        $twigTemplate = $this->twig->load($feedTemplatePath);

        $renderedContent = '';
        foreach ($items as $item) {
            $renderedContent .= $this->getRenderedBlock(
                $twigTemplate,
                'item',
                [
                    'item' => $item,
                    'domainConfig' => $domainConfig,
                ]
            );
        }

        file_put_contents($targetFilepath, $renderedContent, FILE_APPEND);
    }

    /**
     * @param \Twig_TemplateWrapper $twigTemplateWrapper
     * @param string $name
     * @param array $parameters
     * @return string
     */
    private function getRenderedBlock(Twig_TemplateWrapper $twigTemplateWrapper, $name, array $parameters = [])
    {
        if ($twigTemplateWrapper->hasBlock($name)) {
            $templateParameters = $this->twig->mergeGlobals($parameters);
            return $twigTemplateWrapper->renderBlock($name, $templateParameters);
        }

        throw new \Shopsys\FrameworkBundle\Model\Feed\Exception\TemplateBlockNotFoundException($name, $twigTemplateWrapper->getTemplateName());
    }
}
