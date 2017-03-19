<?php

/* layouts\default.twig */
class __TwigTemplate_6ba6e2261c9f66f13ce6422082303e0841257ed8d5838a476afb004f8bbaf5b8 extends Twig_Template
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
        echo twig_escape_filter($this->env, (isset($context["content"]) ? $context["content"] : null), "html", null, true);
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
