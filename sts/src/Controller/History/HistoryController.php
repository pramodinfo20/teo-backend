<?php

namespace App\Controller\History;

use App\Controller\LegacyBaseController;
use App\Form\History\HistoryCommentType;
use App\Form\History\HistorySelectorSearchType;
use App\History\Creators\HistoryTypeStaticCreator;
use App\History\Strategies\HistoryStrategyFactory;
use App\History\Strategies\HistoryStrategyI;
use App\Model\History\HistoryComment;
use App\Model\History\HistoryType;
use App\Model\History\Search\HistorySelector;
use App\Service\History\History;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/history")
 */
class HistoryController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET, POST"}, name="history_index")
     * @param Request                $request
     *
     * @param History                $history
     *
     * @param HistoryStrategyFactory $strategy
     *
     * @return Response
     * @throws \Exception
     */
    public function index
    (
        Request $request,
        History $history,
        HistoryStrategyFactory $strategy
    ): Response
    {
        $search = $request->get('HistorySelectorSearchType', null);
        $typeId = $request->get('type', 0);
        $id = $request->get('id', 0);
        $fk = $request->get('fk', 0);

        $historyType = null;
        $historyStrategy = null;
        $searchResults = null;
        $historyModel = null;
        $historyMetaData = null;
        $historyForm = null;
        $legacyAction = null;
        $log = null;

        if ($typeId != 0) {
            $historyType = HistoryTypeStaticCreator::getHistoryType($typeId);
        }

        $historySelector = new HistorySelector();

        $historyStrategy = $strategy->getHistoryStrategy($typeId);

        $options = [
            'historyTable' => $historyStrategy->getTableName(),
            'fkId' => $fk
        ];

        if (!is_null($search)) {

            $form = $this->createForm(HistorySelectorSearchType::class, $historySelector, $options);
            $form->handleRequest($request);

            $searchResults = $history->getSearchResults($historySelector, $historyStrategy);
            $log = $historyStrategy->isOnlyLog();
        }

        if ($id != 0) {
            $historyStrategy = $strategy->getHistoryStrategy($typeId);
            $historyModel = $historyStrategy->load($id);
            $historyMetaData = $historyStrategy->getMetaData($id);

            $historyForm = $this->createForm(HistorySelectorSearchType::class, $historySelector, $options)
                ->createView();
            $legacyAction = $historyStrategy->getLegacyAction();
        }

        $arguments = [
            'typeId' => $typeId,
            'type' => $historyType,
            'searchResults' => $searchResults,
            'log' => $log,
            'id' => $id,
            'fkId' => $fk,
            'historyModel' => $historyModel,
            'historyMetaData' => $historyMetaData,
            'historyForm' => $historyForm,
            'legacyAction' => $legacyAction
        ];


        return $this->render('History/index.html.twig', $arguments);
    }

    /**
     * @Route("/type/{type}", methods={"GET, POST"}, name="index_with_type")
     * @param Request     $request
     * @param HistoryType $type
     *
     * @return Response
     */
    public function indexWithType
    (
        Request $request,
        HistoryType $type
    ): Response
    {
        return $this->forward('App\Controller\History\HistoryController::index', [
                'type' => $type->getType()
            ]
        );
    }

    /**
     * @Route("/type/{type}/fk/{fk}/search", methods={"POST"}, name="index_with_type_and_search")
     * @param Request     $request
     * @param int         $fk
     * @param HistoryType $type
     *
     * @return Response
     */
    public function indexWithTypeAndFKAndSearch
    (
        Request $request,
        int $fk,
        HistoryType $type
    ): Response
    {
        return $this->forward('App\Controller\History\HistoryController::index', [
            'type' => $type->getType(),
             'fk' => $fk
            ]
        );
    }

    /**
     * @Route("/type/{type}/id/{id}", methods={"GET"}, name="index_with_type_and_id")
     * @param Request     $request
     * @param HistoryType $type
     * @param int         $id
     *
     * @return Response
     */
    public function indexWithTypeAndId
    (
        Request $request,
        HistoryType $type,
        int $id
    ): Response
    {
        return $this->forward('App\Controller\History\HistoryController::index', [
                'type' => $type->getType(),
                'id' => $request->get('id')
            ]
        );
    }

    /**
     * @Route("/type/{type}/id/{id}/fk/{fk}", methods={"GET"}, name="index_with_type_id_and_fk")
     * @param Request     $request
     * @param HistoryType $type
     * @param int         $id
     * @param int         $fk
     *
     * @return Response
     */
    public function indexWithTypeIdAndFk
    (
        Request $request,
        HistoryType $type,
        int $id,
        int $fk
    ): Response
    {
        return $this->forward('App\Controller\History\HistoryController::index', [
                'type' => $type->getType(),
                'id' => $request->get('id'),
                'fk' => $request->get('fk')
            ]
        );
    }



    /**
     * @Route("/strategy/{strategy}/init", methods={"POST"}, name="ajax_initialize_history_entry")
     * @param Request          $request
     * @param HistoryStrategyI $strategy
     *
     * @return Response
     */
    public function initHistoryEntry
    (
        Request $request,
        HistoryStrategyI $strategy
    ): Response
    {
        $comment = new HistoryComment();

        $form = $this->createForm(HistoryCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Save non-entity almost as real entity object but
             * we have to decide how to do this, force developer
             * to use model classes instead of working on controller.
             */
            $strategy->initSession($comment->getId(), $comment->getComment());

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }
}