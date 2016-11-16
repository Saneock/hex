<?php

/* catalog/index.tpl */
class __TwigTemplate_dc96a537976b5abeb8730405fb1fcf5718da9ae28374c89735cb5ee7bf010286 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "I'm catalog index file!

";
        // line 3
        echo twig_escape_filter($this->env, $this->env->getExtension('Hex\Twig_Extensions\IncludeDefault')->include_default("catalog/header.tpl"), "html", null, true);
    }

    public function getTemplateName()
    {
        return "catalog/index.tpl";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  23 => 3,  19 => 1,);
    }

    public function getSource()
    {
        return "";
    }
}
