<script> function filterSearchKeys($query){
    $query = trim(preg_replace("/(\s+)+/", " ", $query));
    $words = array();
    // expand this list with your words.
    $list = array("SQL","JAVA","JAVASCRIPT","JQUERY","PHP","MYSQL","HTML","MANGODB","CSS","AJAX","ANGULAR JS","J2EE","DATABASE","SERVER","LINUX");
    $c = 0;
    foreach(explode(" ", $query) as $key){
        if (in_array($key, $list)){
            continue;
        }
        $words[] = $key;
        if ($c >= 15){
            break;
        }
        $c++;
    }
    return $words;
}

// limit words number of characters
function limitChars($query, $limit = 200){
    return substr($query, 0,$limit);
}

function search($query){

    $query = trim($query);
    if (mb_strlen($query)===0){
        // no need for empty search right?
        return false; 
    }
    $query = limitChars($query);

    // Weighing scores
    $scoreFullTitle = 6;
    $scoreTitleKeyword = 5;
    $scoreFullSummary = 5;
    $scoreSummaryKeyword = 4;
    $scoreFullDocument = 4;
    $scoreDocumentKeyword = 3;
    $scoreCategoryKeyword = 2;
    $scoreUrlKeyword = 1;

    $keywords = filterSearchKeys($query);
    $escQuery = DB::escape($query); // see note above to get db object
    $titleSQL = array();
    $sumSQL = array();
    $docSQL = array();
    $categorySQL = array();
    $urlSQL = array();

    /** Matching full occurences **/
    if (count($keywords) > 1){
        $titleSQL[] = "if (p_title LIKE '%".$escQuery."%',{$scoreFullTitle},0)";
        $sumSQL[] = "if (p_summary LIKE '%".$escQuery."%',{$scoreFullSummary},0)";
        $docSQL[] = "if (p_content LIKE '%".$escQuery."%',{$scoreFullDocument},0)";
    }

    /** Matching Keywords **/
    foreach($keywords as $key){
        $titleSQL[] = "if (p_title LIKE '%".DB::escape($key)."%',{$scoreTitleKeyword},0)";
        $sumSQL[] = "if (p_summary LIKE '%".DB::escape($key)."%',{$scoreSummaryKeyword},0)";
        $docSQL[] = "if (p_content LIKE '%".DB::escape($key)."%',{$scoreDocumentKeyword},0)";
        $urlSQL[] = "if (p_url LIKE '%".DB::escape($key)."%',{$scoreUrlKeyword},0)";
        $categorySQL[] = "if ((
        SELECT count(category.tag_id)
        FROM category
        JOIN post_category ON post_category.tag_id = category.tag_id
        WHERE post_category.post_id = p.post_id
        AND category.name = '".DB::escape($key)."'
                    ) > 0,{$scoreCategoryKeyword},0)";
    }

    // Just incase it's empty, add 0
    if (empty($titleSQL)){
        $titleSQL[] = 0;
    }
    if (empty($sumSQL)){
        $sumSQL[] = 0;
    }
    if (empty($docSQL)){
        $docSQL[] = 0;
    }
    if (empty($urlSQL)){
        $urlSQL[] = 0;
    }
    if (empty($tagSQL)){
        $tagSQL[] = 0;
    }

    $sql = "SELECT p.p_id,p.p_title,p.p_date_published,p.p_url,
            p.p_summary,p.p_content,p.thumbnail,
            (
                (-- Title score
                ".implode(" + ", $titleSQL)."
                )+
                (-- Summary
                ".implode(" + ", $sumSQL)." 
                )+
                (-- document
                ".implode(" + ", $docSQL)."
                )+
                (-- tag/category
                ".implode(" + ", $categorySQL)."
                )+
                (-- url
                ".implode(" + ", $urlSQL)."
                )
            ) as relevance
            FROM JOB_MASTER p
            WHERE p.status = 'A'
            HAVING relevance > 0
            ORDER BY relevance DESC,p.page_views DESC ";
    $results = DB::query($sql);
    if (!$results){
        return false;
    }
    return $results;
}
    </script>