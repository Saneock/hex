<?php

/* page/items.twig */
class __TwigTemplate_a12633162527165960f423d9b4ab139f680d30f40773c53fafbae2fbb1fc8593 extends Twig_Template
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
        echo "<div class=\"items\" style=\"border:1px solid #CCC; padding: 20px; margin: 20px 0;\">
\tItems block ";
        // line 2
        echo (isset($context["page"]) ? $context["page"] : null);
        echo "
</div>";
    }

    public function getTemplateName()
    {
        return "page/items.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  22 => 2,  19 => 1,);
    }

    public function getSource()
    {
        return "";
    }
}
