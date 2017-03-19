<?php

/* layouts\default.twig */
class __TwigTemplate_4d9bb74b94a6594cd0da2e3fe6151c2c12bd2a72b0f0bbd13c5cb5b523c15c4c extends Twig_Template
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
        echo "Header
<br>
";
        // line 3
        echo (isset($context["content"]) ? $context["content"] : null);
        echo "
<br>
Footer";
    }

    public function getTemplateName()
    {
        return "layouts\\default.twig";
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
