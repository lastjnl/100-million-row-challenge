<?php

namespace App;

use App\Commands\Visit;

final class Parser
{
    private const CHUNK_SIZE = 100000;

    public function parse(string $inputPath, string $outputPath): void
    {
        $buffer = '';
        $handle = fopen($inputPath, 'r');
        stream_set_read_buffer($handle, 0);

        $urlToId = [];
        $visits = Visit::all();
        $data = [];
        
        foreach ($visits as $id => $visit) {
            $sliced = substr($visit->uri, 19);
            $urlToId[$sliced] = $id;
        }
       
        while (feof($handle) === false) {
            $buffer .= fread($handle, self::CHUNK_SIZE);
            $lines = explode("\n", $buffer);
            $buffer = array_pop($lines);
            foreach ($lines as $line) {
                $comma = strpos($line, ',');
                $baseUrl = substr($line, 19, $comma - 19);
                $date = substr($line, $comma + 1, 10);
                $id = $urlToId[$baseUrl];
                $data[$id][$date] = ($data[$id][$date] ?? 0) + 1;
            }
        }

        if (!empty($buffer)) {
            $comma = strpos($buffer, ',');
            $id = $urlToId[substr($buffer, 19, $comma - 19)];
            $date = substr($buffer, $comma + 1, 10);
            $data[$id][$date] = ($data[$id][$date] ?? 0) + 1;
        }

        fclose($handle);

        $handle = fopen($outputPath, 'w');
        $output = [];
        foreach ($data as $id => $timestamps) {
            ksort($timestamps);
            $output[substr($visits[$id]->uri, 19)] = $timestamps;
        }
        unset($timestamps);
        fwrite($handle, json_encode($output, JSON_PRETTY_PRINT));
    }
}