<?php

namespace App\Controller;

use App\Entity\Translations;
use App\Entity\TranslationsDomain;
use App\Entity\TranslationsLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/translations")
 */
class TranslationController extends LegacyBaseController
{
    /**
     * @Route("/", name="translations_index", methods={"GET"})
     */
    public function index(): Response
    {
        $translations = $this->getDoctrine()
            ->getRepository(Translations::class)
            ->findAll();

        return $this->render('Translations/index.html.twig', [
            'translations' => $translations,
        ]);
    }

    /**
     * @Route("/language/{language}",
     *     methods={"GET"},
     *     name="translations_index_with_language"
     * )
     * @param TranslationsLanguage $language
     *
     * @return Response
     */
    public function indexWithLanguage(TranslationsLanguage $language): Response
    {
        return $this->forward('App\Controller\TranslationController::index', [
            'language' => $language->getId()
        ]);
    }

    /**
     * @Route("/language/{language}/domain/{domain}",
     *     methods={"GET"},
     *     name="translations_index_with_language_and_domain"
     * )
     * @param TranslationsLanguage $language
     * @param TranslationsDomain   $domain
     *
     * @return Response
     */
    public function indexWithLanguageAndDomain(TranslationsLanguage $language, TranslationsDomain $domain): Response
    {
        return $this->forward('App\Controller\TranslationController::index', [
            'language' => $language->getId(),
            'domain' => $domain->getId()
        ]);
    }
}
