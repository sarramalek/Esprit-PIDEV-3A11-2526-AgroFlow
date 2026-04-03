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

/* about/index.html.twig */
class __TwigTemplate_7398a2b0ef04bca9bd51f0196170bc29 extends Template
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

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'stylesheets' => [$this, 'block_stylesheets'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "about/index.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "about/index.html.twig"));

        $this->parent = $this->load("base.html.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    // line 3
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

        yield "Notre Équipe - AgroFlow";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 5
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

        // line 6
        yield "    ";
        yield from $this->yieldParentBlock("stylesheets", $context, $blocks);
        yield "
    <!-- Optionnel: appel à font-awesome local ou cdn -->
    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\" rel=\"stylesheet\">
    <style>
        .page-about-container {
            background-color: #FCF8E6;
            min-height: 100vh;
            padding: 80px 0;
            font-family: \"Open Sans\", sans-serif;
        }

        .about-title {
            color: #2C3E50;
            font-family: 'Marcellus', serif;
            font-size: 42px;
            text-align: center;
            margin-bottom: 50px;
            font-weight: 700;
        }

        /* --- Section 1 : Portraits --- */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 80px;
        }

        @media (max-width: 991px) {
            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .team-grid {
                grid-template-columns: 1fr;
            }
        }

        .team-portrait-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .team-portrait-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transform: translateY(-3px);
        }

        .team-portrait-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #FCF8E6;
        }

        .team-portrait-name {
            color: #2C3E50;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 5px;
            font-family: 'Marcellus', serif;
        }

        .team-portrait-role {
            color: #666;
            font-size: 14px;
            font-style: italic;
            margin: 0;
        }

        /* --- Section 2 : Pôles de Gestion --- */
        .poles-section {
            background: white;
            border-radius: 12px;
            padding: 40px;
            border: 1px solid #eee;
        }

        .poles-title {
            color: #2C3E50;
            font-family: 'Marcellus', serif;
            font-size: 28px;
            text-align: center;
            margin-bottom: 40px;
        }

        .poles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media (max-width: 991px) {
            .poles-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .poles-grid {
                grid-template-columns: 1fr;
            }
        }

        .pole-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #FCF8E6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .pole-item:hover {
            background: #f4ecce;
        }

        .pole-icon {
            color: #2D5A27;
            font-size: 24px;
        }

        .pole-text {
            display: flex;
            flex-direction: column;
        }

        .pole-gestion {
            color: #2C3E50;
            font-weight: bold;
            font-size: 16px;
        }

        .pole-member {
            color: #666;
            font-size: 13px;
        }
    </style>
";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 155
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

        // line 156
        yield "    <div class=\"page-about-container\">
        <div class=\"container custom-container\" style=\"max-width: 1100px; margin: 0 auto; padding: 0 15px;\">
            <h1 class=\"about-title\">L'Équipe AgroFlow : L'union fait la force</h1>

            <!-- Section 1 : Portraits épurés -->
            <div class=\"team-grid\">
                <div class=\"team-portrait-card\">
                    <img src=\"";
        // line 163
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/img/blog/yessmine.png"), "html", null, true);
        yield "\" alt=\"Yessmine Rezgui\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Yessmine Rezgui</h3>
                    <p class=\"team-portrait-role\">Spécialiste IA & Data</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"";
        // line 168
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/img/blog/blog-inside-post.jpg"), "html", null, true);
        yield "\" alt=\"Eya Mallouli\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Eya Mallouli</h3>
                    <p class=\"team-portrait-role\">Responsable Opérations</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"";
        // line 173
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/img/blog/blog-recent-2.jpg"), "html", null, true);
        yield "\" alt=\"Abdslem Chheider\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Abdslem Chheider</h3>
                    <p class=\"team-portrait-role\">Ingénieur Agronome</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"";
        // line 178
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/img/blog/blog-recent-1.jpg"), "html", null, true);
        yield "\" alt=\"Sarra Malek\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Sarra Malek</h3>
                    <p class=\"team-portrait-role\">Responsable Logistique</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"";
        // line 183
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/img/blog/blog-recent-5.jpg"), "html", null, true);
        yield "\" alt=\"Nourane Landoulsi\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Nourane Landoulsi</h3>
                    <p class=\"team-portrait-role\">Chef de Projet</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"";
        // line 188
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("assets/img/blog/blog-recent-3.jpg"), "html", null, true);
        yield "\" alt=\"Eya Laajimi\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Eya Laajimi</h3>
                    <p class=\"team-portrait-role\">Responsable Communication</p>
                </div>
            </div>

            <!-- Section 2 : Pôles de Gestion -->
            <div class=\"poles-section\">
                <h2 class=\"poles-title\">Nos Pôles de Gestion</h2>
                <div class=\"poles-grid\">
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-cow\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Gestion du Cheptel</span>
                            <span class=\"pole-member\">Yessmine Rezgui</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-stethoscope\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Suivi des tâches</span>
                            <span class=\"pole-member\">Sarra Malek</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-sync-alt\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Rotation des Terrains</span>
                            <span class=\"pole-member\">Nourane Landoulsi</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-warehouse\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Gestion des Stocks</span>
                            <span class=\"pole-member\">Eya Mallouli</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-tools\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Maintenance des Machines</span>
                            <span class=\"pole-member\">Eya Laajimi</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-calendar-check\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Gestion Événementielle</span>
                            <span class=\"pole-member\">Abdslem Chheider
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        return "about/index.html.twig";
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
        return array (  323 => 188,  315 => 183,  307 => 178,  299 => 173,  291 => 168,  283 => 163,  274 => 156,  261 => 155,  101 => 6,  88 => 5,  65 => 3,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}Notre Équipe - AgroFlow{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <!-- Optionnel: appel à font-awesome local ou cdn -->
    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\" rel=\"stylesheet\">
    <style>
        .page-about-container {
            background-color: #FCF8E6;
            min-height: 100vh;
            padding: 80px 0;
            font-family: \"Open Sans\", sans-serif;
        }

        .about-title {
            color: #2C3E50;
            font-family: 'Marcellus', serif;
            font-size: 42px;
            text-align: center;
            margin-bottom: 50px;
            font-weight: 700;
        }

        /* --- Section 1 : Portraits --- */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 80px;
        }

        @media (max-width: 991px) {
            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .team-grid {
                grid-template-columns: 1fr;
            }
        }

        .team-portrait-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .team-portrait-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transform: translateY(-3px);
        }

        .team-portrait-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #FCF8E6;
        }

        .team-portrait-name {
            color: #2C3E50;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 5px;
            font-family: 'Marcellus', serif;
        }

        .team-portrait-role {
            color: #666;
            font-size: 14px;
            font-style: italic;
            margin: 0;
        }

        /* --- Section 2 : Pôles de Gestion --- */
        .poles-section {
            background: white;
            border-radius: 12px;
            padding: 40px;
            border: 1px solid #eee;
        }

        .poles-title {
            color: #2C3E50;
            font-family: 'Marcellus', serif;
            font-size: 28px;
            text-align: center;
            margin-bottom: 40px;
        }

        .poles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media (max-width: 991px) {
            .poles-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .poles-grid {
                grid-template-columns: 1fr;
            }
        }

        .pole-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #FCF8E6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .pole-item:hover {
            background: #f4ecce;
        }

        .pole-icon {
            color: #2D5A27;
            font-size: 24px;
        }

        .pole-text {
            display: flex;
            flex-direction: column;
        }

        .pole-gestion {
            color: #2C3E50;
            font-weight: bold;
            font-size: 16px;
        }

        .pole-member {
            color: #666;
            font-size: 13px;
        }
    </style>
{% endblock %}

{% block body %}
    <div class=\"page-about-container\">
        <div class=\"container custom-container\" style=\"max-width: 1100px; margin: 0 auto; padding: 0 15px;\">
            <h1 class=\"about-title\">L'Équipe AgroFlow : L'union fait la force</h1>

            <!-- Section 1 : Portraits épurés -->
            <div class=\"team-grid\">
                <div class=\"team-portrait-card\">
                    <img src=\"{{ asset('assets/img/blog/yessmine.png') }}\" alt=\"Yessmine Rezgui\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Yessmine Rezgui</h3>
                    <p class=\"team-portrait-role\">Spécialiste IA & Data</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"{{ asset('assets/img/blog/blog-inside-post.jpg') }}\" alt=\"Eya Mallouli\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Eya Mallouli</h3>
                    <p class=\"team-portrait-role\">Responsable Opérations</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"{{ asset('assets/img/blog/blog-recent-2.jpg') }}\" alt=\"Abdslem Chheider\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Abdslem Chheider</h3>
                    <p class=\"team-portrait-role\">Ingénieur Agronome</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"{{ asset('assets/img/blog/blog-recent-1.jpg') }}\" alt=\"Sarra Malek\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Sarra Malek</h3>
                    <p class=\"team-portrait-role\">Responsable Logistique</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"{{ asset('assets/img/blog/blog-recent-5.jpg') }}\" alt=\"Nourane Landoulsi\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Nourane Landoulsi</h3>
                    <p class=\"team-portrait-role\">Chef de Projet</p>
                </div>
                <div class=\"team-portrait-card\">
                    <img src=\"{{ asset('assets/img/blog/blog-recent-3.jpg') }}\" alt=\"Eya Laajimi\" class=\"team-portrait-img\">
                    <h3 class=\"team-portrait-name\">Eya Laajimi</h3>
                    <p class=\"team-portrait-role\">Responsable Communication</p>
                </div>
            </div>

            <!-- Section 2 : Pôles de Gestion -->
            <div class=\"poles-section\">
                <h2 class=\"poles-title\">Nos Pôles de Gestion</h2>
                <div class=\"poles-grid\">
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-cow\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Gestion du Cheptel</span>
                            <span class=\"pole-member\">Yessmine Rezgui</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-stethoscope\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Suivi des tâches</span>
                            <span class=\"pole-member\">Sarra Malek</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-sync-alt\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Rotation des Terrains</span>
                            <span class=\"pole-member\">Nourane Landoulsi</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-warehouse\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Gestion des Stocks</span>
                            <span class=\"pole-member\">Eya Mallouli</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-tools\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Maintenance des Machines</span>
                            <span class=\"pole-member\">Eya Laajimi</span>
                        </div>
                    </div>
                    <div class=\"pole-item\">
                        <div class=\"pole-icon\"><i class=\"fa-solid fa-calendar-check\"></i></div>
                        <div class=\"pole-text\">
                            <span class=\"pole-gestion\">Gestion Événementielle</span>
                            <span class=\"pole-member\">Abdslem Chheider
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
", "about/index.html.twig", "C:\\Users\\Yessmine\\PIweb\\templates\\about\\index.html.twig");
    }
}
