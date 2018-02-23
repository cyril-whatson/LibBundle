<?php

namespace WH\LibBundle\Services;

use League\Csv\Reader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UsefulFunctions
 *
 * @package WH\LibBundle\Services
 */
class CsvImporter
{

    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param UploadedFile $file
     * @param              $subfolderPath
     *
     * @return string
     */
    public function moveFile(UploadedFile $file, $subfolderPath)
    {
        $fileDestinationFolder = $this->container->get('kernel')->getRootDir() . '/../transit/IN/import/';
        $fileDestinationFolder .= $subfolderPath . '/';
        $newFileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($fileDestinationFolder, $newFileName);

        return $fileDestinationFolder . $newFileName;
    }

    /**
     * @param        $filePath
     * @param array  $columns
     * @param array  $options
     * @param string $delimiter
     *
     * @return array
     */
    public function getCsvData($filePath, array $columns, $options = [], $delimiter = ';',$header = true)
    {
        $file = new File($filePath);

        $mimeType = $file->getMimeType();

        switch ($mimeType) {
            case 'application/vnd.ms-excel':
            case 'application/vnd.ms-office':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject($filePath);
                $csvFilePath = preg_replace('#\.(xls)(.*)#', '.csv', $filePath);

                \PHPExcel_IOFactory::createWriter($phpExcelObject, 'CSV')
                    ->setDelimiter(';')
                    ->save($csvFilePath);
                break;

            case 'text/plain':
                $csvFilePath = $filePath;
                break;

            default:
                return [
                    'errors' => [
                        'Non supported file type',
                    ],
                ];
                break;
        }

        $csv = Reader::createFromPath($csvFilePath);
        $csv->setDelimiter($delimiter);

        $data = $csv->fetchAll();

        $errors = [];

        $columnedData = [];
        $uniqueColumns = [];

        // Détection CSV vide
        if (!isset($data[0])) {
            $errors[] = 'Empty CSV file';
        } else {
            $csvColumns = $data[0];

            // Supprime la 1er ligne d'entête
            if($header) {
                unset($data[0]);
            }

            if (sizeof($columns) != sizeof($csvColumns)) {
                $errors[] = 'Columns number is incorrect';
            }

            // Détection 1ère ligne d'entête incorrecte
            if(isset($options['checkHeading'])) {
                $diffColumns = array_diff($columns, $csvColumns);
                if (!empty($diffColumns)) {
                    foreach ($diffColumns as $diffColumn) {
                        $errors[] = 'Column "' . $diffColumn . '" has not been detected';
                    }
                }
            }
            // Vérification que les colonnes uniques pour vérifier les doublons sont bien présentes dans les colonnes d'entête
            if (isset($options['uniqueColumns'])) {
                $uniqueColumns = $options['uniqueColumns'];

                // Si la colonne est unique, on est pas obligé d'envoyer un tableau, une chaine suffit
                if (!is_array($uniqueColumns)) {
                    $uniqueColumns = [$uniqueColumns];
                }

                foreach ($uniqueColumns as $uniqueColumn) {
                    if (!in_array($uniqueColumn, $columns)) {
                        $errors[] = 'Verification column "' . $uniqueColumn . '" has not been detected';
                    }
                }
            }
        }

        if (empty($errors)) {
            $perfectDuplicationsHashes = [];
            $uniqueColumnsDuplicationsHashes = [];

            // Parcours du CSV
            foreach ($data as $key => $values) {
                $lineNumber = $key + 1;

                // Détection doublon parfait
                $perfectDuplicationHash = hash('sha1', implode('', $values));

                if (isset($perfectDuplicationsHashes[$perfectDuplicationHash])) {
                    // Eventuellement on pourra afficher le nombre de fois où le doublon est présente
                    $perfectDuplicationsHashes[$perfectDuplicationHash]++;
                    $errors[] = 'Line number ' . $lineNumber . ' is a perfect duplication';
                    continue;
                } else {
                    $perfectDuplicationsHashes[$perfectDuplicationHash] = 1;
                }

                // Détection doublon selon colonnes demandées
                if (!empty($uniqueColumns)) {
                    // Fabrication du hash
                    $columnsDuplicationHash = '';
                    foreach ($uniqueColumns as $uniqueColumn) {
                        $uniqueColumnKey = array_search($uniqueColumn, $columns);
                        $columnsDuplicationHash .= $values[$uniqueColumnKey];
                    }
                    $columnsDuplicationHash = hash('sha1', $columnsDuplicationHash);

                    if (isset($uniqueColumnsDuplicationsHashes[$columnsDuplicationHash])) {
                        // Eventuellement on pourra afficher le nombre de fois où le doublon est présente
                        $uniqueColumnsDuplicationsHashes[$columnsDuplicationHash]++;
                        $errors[] = 'Line number ' . $lineNumber . ' is a duplication';
                        continue;
                    } else {
                        $uniqueColumnsDuplicationsHashes[$columnsDuplicationHash] = 1;
                    }
                }

                $rowData = [];
                foreach ($values as $value) {
                    $rowData[] = $value;
                }
                if (sizeof($columns) == sizeof($rowData)) {
                    $columnedData[] = array_combine($columns, $rowData);
                }
            }
        }

        // S'il y a des erreurs, on ne retourne que ça
        if (!empty($errors)) {
            return [
                'errors' => $errors,
            ];
        }

        // Sinon on récupère les données
        return [
            'data' => $columnedData,
        ];
    }

}
