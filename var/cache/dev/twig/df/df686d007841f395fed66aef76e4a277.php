<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* base.html.twig */
class __TwigTemplate_8a6fcac4337d79cca1bca0e21aab4ed4 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'stylesheets' => [$this, 'block_stylesheets'],
            'javascripts' => [$this, 'block_javascripts'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "base.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "base.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>";
        // line 6
        yield from $this->unwrap()->yieldBlock('title', $context, $blocks);
        yield "</title>
        
        ";
        // line 8
        yield from $this->unwrap()->yieldBlock('stylesheets', $context, $blocks);
        // line 86
        yield "
        ";
        // line 87
        yield from $this->unwrap()->yieldBlock('javascripts', $context, $blocks);
        // line 89
        yield "    </head>
    <body class=\"index-page\">
        <!-- Navbar -->
        <header id=\"header\" class=\"header\" style=\"position: sticky; top: 0; z-index: 1000; background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 30px; display: flex; align-items: center; justify-content: space-between;\">
            <div style=\"display: flex; align-items: center; gap: 10px;\">
                <a href=\"/\" style=\"display: flex; align-items: center; text-decoration: none; gap: 10px;\">
                    <img src=\"";
        // line 95
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/img/logo.png"), "html", null, true);
        yield "\" alt=\"Logo\" style=\"height: 35px;\">
                    <span style=\"font-size: 24px; font-weight: 700; color: #2C3E50; font-family: 'Marcellus', serif; letter-spacing: 1px;\">AGROFLOW</span>
                </a>
            </div>
            
            <nav id=\"navmenu\" class=\"navmenu\" style=\"display: flex; gap: 30px; align-items: center;\">
                <a href=\"/\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Accueil</a>
                <a href=\"/#services\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Services</a>
                <a href=\"/#posts\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Publications</a>
                <a href=\"/#testimonials\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Avis</a>
                <a href=\"/#ads\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Tutos & Partenaires</a>
                <a href=\"";
        // line 106
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_about");
        yield "\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Notre Équipe</a>
            </nav>
            
            <div style=\"display: flex; gap: 15px; align-items: center;\">
                <a href=\"#\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px;\">Connexion</a>
                <a href=\"#\" style=\"background-color: #2D5A27; color: white; padding: 10px 25px; border-radius: 5px; text-decoration: none; font-weight: 600; font-size: 15px; transition: background 0.3s;\">S'inscrire</a>
            </div>
        </header>

        <div class=\"main-container\">
            <!-- Sidebar -->
            <aside class=\"sidebar-nature\" style=\"display: none;\">
                <nav>
                    <ul>
                        <li><a href=\"";
        // line 120
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_animaux_index");
        yield "\">Nos Animaux</a></li>
                        <li><a href=\"";
        // line 121
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_examens_index");
        yield "\">Examens</a></li>
                    </ul>
                </nav>
            </aside>

            <!-- Contenu central -->
            <main class=\"content-area\" style=\"flex: 1; padding: 0;\">
                ";
        // line 128
        yield from $this->unwrap()->yieldBlock('body', $context, $blocks);
        // line 131
        yield "            </main>
        </div>
    </body>
</html>";
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        yield from [];
    }

    // line 6
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        yield "AgroFlow";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 8
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_stylesheets(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "stylesheets"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "stylesheets"));

        // line 9
        yield "            <link rel=\"stylesheet\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/css/main.css"), "html", null, true);
        yield "\">
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #FCF8E6; /* Jaune Pâle */
                    display: flex;
                    flex-direction: column;
                    min-height: 100vh;
                    font-family: Arial, sans-serif;
                }
                
                /* Navbar Haut */
                .navbar-tech {
                    background-color: #2C3E50; /* Bleu Tech */
                    color: white;
                    padding: 15px 30px;
                    display: flex;
                    align-items: center;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    z-index: 10;
                }
                .navbar-tech .brand {
                    font-size: 1.5rem;
                    font-weight: bold;
                    color: white;
                    text-decoration: none;
                }

                /* Container Principal */
                .main-container {
                    display: flex;
                    flex: 1;
                }

                /* Sidebar Gauche */
                .sidebar-nature {
                    background-color: #2D5A27; /* Vert Nature */
                    width: 250px;
                    padding: 20px 0;
                    color: white;
                    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
                }
                .sidebar-nature ul {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                }
                .sidebar-nature li {
                    margin: 0;
                }
                .sidebar-nature a {
                    color: #FCF8E6; /* Jaune Pâle pour le texte pour un bon contraste */
                    display: block;
                    padding: 15px 25px;
                    text-decoration: none;
                    font-size: 1.1rem;
                    border-left: 4px solid transparent;
                    transition: all 0.3s ease;
                }
                .sidebar-nature a:hover {
                    background-color: rgba(255, 255, 255, 0.1);
                    border-left: 4px solid #FCF8E6;
                }

                /* Contenu Central */
                .content-area {
                    flex: 1;
                    padding: 30px;
                    background-color: #FCF8E6; /* Jaune Pâle */
                }
                /* Style ajouté pour le hover du menu */
                .navmenu a:hover {
                    color: #2D5A27 !important;
                }
            </style>
        ";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 87
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_javascripts(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "javascripts"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "javascripts"));

        // line 88
        yield "        ";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 128
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_body(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        // line 129
        yield "                    <!-- Le contenu spécifique à la page va s'afficher ici -->
                ";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "base.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  302 => 129,  289 => 128,  278 => 88,  265 => 87,  176 => 9,  163 => 8,  140 => 6,  126 => 131,  124 => 128,  114 => 121,  110 => 120,  93 => 106,  79 => 95,  71 => 89,  69 => 87,  66 => 86,  64 => 8,  59 => 6,  52 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>{% block title %}AgroFlow{% endblock %}</title>
        
        {% block stylesheets %}
            <link rel=\"stylesheet\" href=\"{{ asset('assets/css/main.css') }}\">
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #FCF8E6; /* Jaune Pâle */
                    display: flex;
                    flex-direction: column;
                    min-height: 100vh;
                    font-family: Arial, sans-serif;
                }
                
                /* Navbar Haut */
                .navbar-tech {
                    background-color: #2C3E50; /* Bleu Tech */
                    color: white;
                    padding: 15px 30px;
                    display: flex;
                    align-items: center;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    z-index: 10;
                }
                .navbar-tech .brand {
                    font-size: 1.5rem;
                    font-weight: bold;
                    color: white;
                    text-decoration: none;
                }

                /* Container Principal */
                .main-container {
                    display: flex;
                    flex: 1;
                }

                /* Sidebar Gauche */
                .sidebar-nature {
                    background-color: #2D5A27; /* Vert Nature */
                    width: 250px;
                    padding: 20px 0;
                    color: white;
                    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
                }
                .sidebar-nature ul {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                }
                .sidebar-nature li {
                    margin: 0;
                }
                .sidebar-nature a {
                    color: #FCF8E6; /* Jaune Pâle pour le texte pour un bon contraste */
                    display: block;
                    padding: 15px 25px;
                    text-decoration: none;
                    font-size: 1.1rem;
                    border-left: 4px solid transparent;
                    transition: all 0.3s ease;
                }
                .sidebar-nature a:hover {
                    background-color: rgba(255, 255, 255, 0.1);
                    border-left: 4px solid #FCF8E6;
                }

                /* Contenu Central */
                .content-area {
                    flex: 1;
                    padding: 30px;
                    background-color: #FCF8E6; /* Jaune Pâle */
                }
                /* Style ajouté pour le hover du menu */
                .navmenu a:hover {
                    color: #2D5A27 !important;
                }
            </style>
        {% endblock %}

        {% block javascripts %}
        {% endblock %}
    </head>
    <body class=\"index-page\">
        <!-- Navbar -->
        <header id=\"header\" class=\"header\" style=\"position: sticky; top: 0; z-index: 1000; background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 30px; display: flex; align-items: center; justify-content: space-between;\">
            <div style=\"display: flex; align-items: center; gap: 10px;\">
                <a href=\"/\" style=\"display: flex; align-items: center; text-decoration: none; gap: 10px;\">
                    <img src=\"{{ asset('assets/img/logo.png') }}\" alt=\"Logo\" style=\"height: 35px;\">
                    <span style=\"font-size: 24px; font-weight: 700; color: #2C3E50; font-family: 'Marcellus', serif; letter-spacing: 1px;\">AGROFLOW</span>
                </a>
            </div>
            
            <nav id=\"navmenu\" class=\"navmenu\" style=\"display: flex; gap: 30px; align-items: center;\">
                <a href=\"/\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Accueil</a>
                <a href=\"/#services\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Services</a>
                <a href=\"/#posts\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Publications</a>
                <a href=\"/#testimonials\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Avis</a>
                <a href=\"/#ads\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Tutos & Partenaires</a>
                <a href=\"{{ path('app_about') }}\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s;\">Notre Équipe</a>
            </nav>
            
            <div style=\"display: flex; gap: 15px; align-items: center;\">
                <a href=\"#\" style=\"color: #2C3E50; text-decoration: none; font-weight: 600; font-size: 15px;\">Connexion</a>
                <a href=\"#\" style=\"background-color: #2D5A27; color: white; padding: 10px 25px; border-radius: 5px; text-decoration: none; font-weight: 600; font-size: 15px; transition: background 0.3s;\">S'inscrire</a>
            </div>
        </header>

        <div class=\"main-container\">
            <!-- Sidebar -->
            <aside class=\"sidebar-nature\" style=\"display: none;\">
                <nav>
                    <ul>
                        <li><a href=\"{{ path('app_animaux_index') }}\">Nos Animaux</a></li>
                        <li><a href=\"{{ path('app_examens_index') }}\">Examens</a></li>
                    </ul>
                </nav>
            </aside>

            <!-- Contenu central -->
            <main class=\"content-area\" style=\"flex: 1; padding: 0;\">
                {% block body %}
                    <!-- Le contenu spécifique à la page va s'afficher ici -->
                {% endblock %}
            </main>
        </div>
    </body>
</html>", "base.html.twig", "C:\\Users\\Yessmine\\PIweb\\templates\\base.html.twig");
    }
}
