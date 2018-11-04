<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Code as CodeEntity;
use App\Utils\Generator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class Code extends AbstractController
{
    /**
     * @var Generator
     */
    private $generator;

    public function __construct(Generator $generator)
    {

        $this->generator = $generator;
    }

    public function generate($nb = 1, $export = null)
    {
        $entityManager = $this->getDoctrine()->getManager();
        try {
            $codes = $this->generator->generateCode($nb);

            foreach ($codes as $number) {
                $code = new CodeEntity();
                $code->setCode($number);
                $code->setDate(new \DateTime());

                $entityManager->persist($code);

            }
            $entityManager->flush();
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->getDoctrine()->resetManager();
            return $this->forward('App\Controller\Code::generate', array(
                'nb'  => $nb,
                'export' => $export
            ));
        }

        if ($export === 'xls') {
            $spreadsheet = new Spreadsheet();

            /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setTitle("code");
            $i = 1;
            foreach ($codes as $number) {
                $sheet->setCellValue('A'.$i, $number);
                $i++;
            }

            $writer = new Xlsx($spreadsheet);

            $fileName = 'codes.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);

            $writer->save($temp_file);

            return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        }

        $response = new Response();
        $response->setContent(json_encode(array(
            'codes' => $codes,
        )));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function getCode($code)
    {
        $entity_code = $this->getDoctrine()
            ->getRepository(CodeEntity::class)
            ->findOneBy(['code' => htmlspecialchars($code)]);

        if (!$entity_code) {
            throw $this->createNotFoundException(
                'Code not found ' . $entity_code
            );
        }
        $response = new Response();

        $response->setContent(json_encode(array(
            'id' => $entity_code->getId(),
            'code' => $entity_code->getCode(),
            'time' => $entity_code->getDate()
        )));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
