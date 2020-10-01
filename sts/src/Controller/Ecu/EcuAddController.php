<?php


namespace App\Controller\Ecu;


use App\Controller\LegacyBaseController;
use App\Entity\ConfigurationEcus;
use App\Entity\EbomParts;
use App\Entity\EcusStsPartNumberMapping;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * @Route("/ecu")
 */
class EcuAddController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="add_ecu_index")
     * @return Response
     */
    public function index(): Response
    {
        $stsPartNumbers = $this->getManager()->getRepository(EbomParts::class)->findAll();

        $parts = [];

        $parts = array_map(function ($item) {
            return [
                'id' => $item->getEbomPartId(),
                'text' => $item->getStsPartNumber()
            ];
        }, $stsPartNumbers);

        $allParts = [
            'id' => 1,
            'text' => 'All Parts',
            'children' => $parts
        ];

        return $this->render('Ecu/Add/index.html.twig', [
            'parts' => $allParts
        ]);
    }


    /**
     * @Route("/create", methods={"POST"}, name="ajax_create_ecu")
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function createEcu(Request $request, TranslatorInterface $translator): Response
    {
        $ecuName = $request->get('ecuName');
        $ecus = $this->getManager()->getRepository(ConfigurationEcus::class)->findOneBy(['ecuName' => $ecuName]);

        if (is_null($ecus)) {
            $configurationEcu = new ConfigurationEcus();
            $configurationEcu->setEcuName($ecuName);
            $configurationEcu->setDiagnosticSoftwareSupportsStsOdx2ForThisEcu(true);
            $this->getManager()->persist($configurationEcu);

            $parts = $request->get('parts');

            if (!is_null($parts)) {
                foreach ($parts as $number) {
                    $ecuPartNumberMapping = new EcusStsPartNumberMapping();
                    $ecuPartNumberMapping->setCeEcu($configurationEcu);
                    $ecuPartNumberMapping->setEbomPart($this->getManager()->getRepository(EbomParts::class)->find($number));
                    $this->getManager()->persist($ecuPartNumberMapping);
                }
            }

            $this->getManager()->flush();

            return $this->renderSuccessJson();
        } else {
            return $this->json(['errors' => [
                $translator->trans('ecu.add.twig.index.name', [], 'messages') =>
                    $translator->trans('ecu.add.twig.dialog.error', [], 'messages')
            ]]);
        }
    }
}