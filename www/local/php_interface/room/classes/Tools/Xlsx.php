<?php

namespace Room\Tools;

/**
 * Чтение xlsx без сторонних библиотек
 *
 * xlsx - это zip с несколькими файлами XML, поэтому хватает ZipArchive
 * и SimpleXML, которые в сборке под Битрикс есть всегда. Ставить
 * PhpSpreadsheet ради разбора трёх справочников почты незачем
 *
 * Поддерживается ровно то, что нужно для справочников: строки, числа
 * и общие строки (sharedStrings). Формулы, даты и стили игнорируются
 */
class Xlsx
{
    /** @var \ZipArchive */
    private $zip;

    /** @var array */
    private $shared = [];

    /**
     * @param string $path
     * @throws \Exception
     */
    public function __construct(string $path)
    {
        if (!class_exists('\ZipArchive')) {
            throw new \Exception('Не найдено расширение PHP zip, читать xlsx нечем');
        }

        if (!is_file($path)) {
            throw new \Exception('Файл не найден: ' . $path);
        }

        $this->zip = new \ZipArchive();

        if ($this->zip->open($path) !== true) {
            throw new \Exception('Не удалось открыть xlsx: ' . $path);
        }

        $this->shared = $this->readSharedStrings();
    }

    public function __destruct()
    {
        if ($this->zip) {
            $this->zip->close();
        }
    }

    /**
     * Листы книги: название => путь к XML внутри архива
     *
     * @return array
     * @throws \Exception
     */
    public function getSheets(): array
    {
        $workbook = $this->xml('xl/workbook.xml');
        $rels = $this->xml('xl/_rels/workbook.xml.rels');

        $targets = [];

        foreach ($rels->Relationship as $rel) {
            $targets[(string)$rel['Id']] = (string)$rel['Target'];
        }

        $sheets = [];
        $ns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        foreach ($workbook->sheets->sheet as $sheet) {
            $id = (string)$sheet->attributes($ns)['id'];
            $target = $targets[$id] ?? '';

            if ($target === '') {
                continue;
            }

            if (strpos($target, 'xl/') !== 0) {
                $target = 'xl/' . ltrim($target, '/');
            }

            $sheets[(string)$sheet['name']] = $target;
        }

        return $sheets;
    }

    /**
     * Путь к листу по части названия, без учёта регистра и диакритики
     *
     * Названия листов в файлах почты содержат диакритику ("detail SČ",
     * "PSČ_POBOXov"), поэтому сравнивать их буквально ненадёжно
     *
     * @param string $needle
     * @return string
     * @throws \Exception
     */
    public function findSheet(string $needle): string
    {
        $needleKey = self::normalizeName($needle);

        foreach ($this->getSheets() as $name => $path) {
            if (strpos(self::normalizeName($name), $needleKey) !== false) {
                return $path;
            }
        }

        return '';
    }

    /**
     * Название листа без диакритики и регистра
     *
     * @param string $name
     * @return string
     */
    private static function normalizeName(string $name): string
    {
        $map = [
            'á' => 'a', 'ä' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e',
            'í' => 'i', 'ĺ' => 'l', 'ľ' => 'l', 'ň' => 'n', 'ó' => 'o',
            'ô' => 'o', 'ŕ' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u',
            'ý' => 'y', 'ž' => 'z',
        ];

        return strtr(mb_strtolower(trim($name)), $map);
    }

    /**
     * Строки листа: каждая - массив значений с нуля по колонкам
     *
     * @param string $path путь к XML листа внутри архива
     * @return \Generator
     * @throws \Exception
     */
    public function rows(string $path): \Generator
    {
        $sheet = $this->xml($path);

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            $max = -1;

            foreach ($row->c as $cell) {
                $index = self::columnIndex((string)$cell['r']);
                $type = (string)$cell['t'];

                if ($type === 'inlineStr') {
                    $value = isset($cell->is) ? trim((string)$cell->is->t) : '';
                } elseif ($type === 's') {
                    $value = $this->shared[(int)$cell->v] ?? '';
                } else {
                    $value = isset($cell->v) ? trim((string)$cell->v) : '';
                }

                $cells[$index] = $value;

                if ($index > $max) {
                    $max = $index;
                }
            }

            $result = [];

            for ($i = 0; $i <= $max; $i++) {
                $result[$i] = $cells[$i] ?? '';
            }

            yield $result;
        }
    }

    /**
     * Общие строки книги
     *
     * @return array
     */
    private function readSharedStrings(): array
    {
        $data = $this->zip->getFromName('xl/sharedStrings.xml');

        if ($data === false) {
            return [];
        }

        $xml = simplexml_load_string($data);

        if ($xml === false) {
            return [];
        }

        $strings = [];

        foreach ($xml->si as $si) {
            $text = '';

            // Строка может быть разбита на куски с разным оформлением
            foreach ($si->xpath('.//*[local-name()="t"]') as $part) {
                $text .= (string)$part;
            }

            $strings[] = trim($text);
        }

        return $strings;
    }

    /**
     * @param string $path
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    private function xml(string $path): \SimpleXMLElement
    {
        $data = $this->zip->getFromName($path);

        if ($data === false) {
            throw new \Exception('В архиве нет файла ' . $path);
        }

        $xml = simplexml_load_string($data);

        if ($xml === false) {
            throw new \Exception('Не удалось разобрать XML: ' . $path);
        }

        return $xml;
    }

    /**
     * Номер колонки по ссылке на ячейку: "A1" -> 0, "AB7" -> 27
     *
     * @param string $ref
     * @return int
     */
    private static function columnIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $match);

        $letters = $match[1] ?? 'A';
        $index = 0;

        for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }
}
