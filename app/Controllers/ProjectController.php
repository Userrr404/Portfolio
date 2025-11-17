<?php

class ProjectController
{
    private ProjectModel $model;

    public function __construct()
    {
        require_once ROOT_PATH . "app/Models/ProjectModel.php";
        $this->model = new ProjectModel();
    }

    /**
     * Controller entry point for projects listing page
     */
    public function index()
    {
        // Get request inputs
        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage  = 12;
        $offset   = ($page - 1) * $perPage;
        $tech     = $_GET['tech'] ?? null;
        $featured = isset($_GET['featured']) ? true : false;

        // Prepare query parameters
        $params = [
            "offset"   => $offset,
            "limit"    => $perPage,
            "tech"     => $tech,
            "featured" => $featured
        ];

        // Get data — model handles DB + CacheService fallback
        $result = $this->model->fetchActiveProjects($params);

        // Extract items + total count
        $projects   = $result["items"];
        $totalCount = $result["total"];
        $totalPages = (int) ceil($totalCount / $perPage);

        // Extra data
        $techList = [];
        foreach ($projects as $p) {
            $techList[$p['id']] = $this->model->getTechList($p['id']);
        }

        // Data passed to view
        return [
            "projects"   => $projects,
            "techList"   => $techList,
            "page"       => $page,
            "totalPages" => $totalPages,
            "perPage"    => $perPage,
            "total"      => $totalCount,
            "filters"    => [
                "tech"     => $tech,
                "featured" => $featured
            ]
        ];
    }
}
