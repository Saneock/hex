<?php

/* page/index.twig */
class __TwigTemplate_38bc716eecc5a82c72a24d1535e1f95f1651917e6615b8289dce950ec189f757 extends Twig_Template
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
        echo "Well done! The page is \"";
        echo (isset($context["page"]) ? $context["page"] : null);
        echo "\"
";
        // line 2
        echo $this->env->getExtension('Hex\Twig_Extensions\Execute')->execute("page/items", array(0 => "value"));
        echo "
<h1>Hello</h1>";
    }

    public function getTemplateName()
    {
        return "page/index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  24 => 2,  19 => 1,);
    }

    public function getSource()
    {
        return "";
    }
}
