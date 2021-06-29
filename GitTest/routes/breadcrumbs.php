<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

// Contractor
Breadcrumbs::for('contractor.index', function ($trail) {
    $trail->push('Home', route('home'));
    $trail->push('請負会社', route('contractor.index'));
});

Breadcrumbs::for('contractor.show', function ($trail, $contractor) {
    $trail->parent('contractor.index');

    $id   = request('id', 0);
    $trail->push($contractor->name, route('contractor.show', ['id' => $id ]));
});

// Job
Breadcrumbs::for('job.index', function ($trail) {
    $trail->push("Home", route('home'));
    $trail->push("求人", route('job.index'));
});
// Job > search
Breadcrumbs::for('job.search', function ($trail) {
    $trail->parent('job.index');
    $trail->push("仕事を探す", route('job.search'));
});
// Job > history
Breadcrumbs::for('job.history', function ($trail) {
    $trail->parent('job.index');
    $trail->push("作成した仕事", route('job.history'));
});
// Job > show
Breadcrumbs::for('job.show', function ($trail) {
    $trail->parent('job.index');
    $trail->push("詳細");
});
// Job > create
Breadcrumbs::for('job.create', function ($trail) {
    $trail->parent('job.history');
    $trail->push("新しい仕事", route('job.create'));
});
// Job > edit
Breadcrumbs::for('job.edit', function ($trail) {
    $trail->parent('job.history');

    $id = request('id', 0);
    $trail->push("詳細", route('job.show',[ 'id'=> $id ]));
    $trail->push("編集");
});
// Job > copy
Breadcrumbs::for('job.copy', function ($trail) {
    $trail->parent('job.create');

    $id   = request('id', 0);
    $trail->push("コピー", route('job.copy',[ 'id' => $id ]));
});
// Job > current
Breadcrumbs::for('job.current', function ($trail) {
    $trail->parent('job.index');
    $trail->push("登録中の仕事", route('job.current'));
});
// Job > message
Breadcrumbs::for('job-message.index', function ($trail) {
    $trail->parent('job.index');
    $trail->push("新着メッセージ", route('job-message.index'));
});
// Job > message (HTTP POST)
Breadcrumbs::for('job-message.search', function ($trail) {
    $trail->parent('job-message.index');
});
// Job > message > inbox
Breadcrumbs::for('job-message.inbox', function ($trail) {
    $trail->parent('job-message.index');
    $trail->push("受信箱", route('job-message.inbox'));
});
// Job > message > inbox > unread
Breadcrumbs::for('job-message.unread', function ($trail) {
    $trail->parent('job-message.inbox');
    $trail->push("未読", route('job-message.unread'));
});
// Job > message > outbox
Breadcrumbs::for('job-message.outbox', function ($trail) {
    $trail->parent('job-message.index');
    $trail->push("送信箱", route('job-message.outbox'));
});

// Job > Profile
Breadcrumbs::for('profile.index', function ($trail) {
    $trail->parent('job.index');
    $trail->push("プロファイル", route('profile.index'));
});
// Job > Profile > Edit
Breadcrumbs::for('profile.edit', function ($trail) {
    $trail->parent('profile.index');
    $trail->push("編集", route('profile.edit'));
});
// Job > Profile > Update
Breadcrumbs::for('profile.update', function ($trail) {
    $trail->parent('profile.edit');
});

// Project
Breadcrumbs::for('project.index', function ($trail) {
    $trail->push('Home', route('home'));
    $trail->push('案件', route('project.index'));
});
Breadcrumbs::for('project.show', function ($trail, $project) {
    $trail->parent('project.index');

    $id   = request('id', 0);
    $trail->push("詳細", route('project.show', ['id' => $id ]));
});

// Scheme
Breadcrumbs::for('scheme.show', function ($trail, $project) {
    $trail->parent('project.show', $project);

    $id   = request('id', 0);
    $trail->push("計画工程表", route('scheme.show', ['id' => $id ]));
});
Breadcrumbs::for('scheme.edit', function ($trail, $project) {
    $trail->parent('scheme.show', $project);

    $id   = request('id', 0);
    $trail->push("編集", route('scheme.edit', ['id' => $id ]));
});

// Task
Breadcrumbs::for('task.show', function ($trail, $project) {
    $trail->parent('project.show', $project);

    $id   = request('id', 0);
    $trail->push("実施工程表", route('task.show', ['id' => $id ]));
});
Breadcrumbs::for('task.edit', function ($trail, $project) {
    $trail->parent('task.show', $project);

    $id   = request('id', 0);
    $trail->push("編集", route('task.edit', ['id' => $id ]));
});

// Gantt
Breadcrumbs::for('gantt', function ($trail) {
    $trail->parent('home');
    $trail->push('工程管理', route('gantt'));
});

// Gantt > Show
Breadcrumbs::for('gantt/show', function ($trail) {
    $trail->parent('gantt');

    $id   = request('id', 0);
    $trail->push(sprintf('工程 %d', $id), route('gantt/show', ['id' => $id ]));
});

// Gantt > Edit
Breadcrumbs::for('gantt/edit', function ($trail) {
    $trail->parent('gantt');

    $id   = request('id', 0);
    $trail->push(sprintf('工程 %d', $id), route('gantt/show', ['id' => $id ]));
    $trail->push('編集');
});

// Gantt > Create Child
Breadcrumbs::for('gantt/create', function ($trail) {
    $trail->parent('gantt');

    $id  = request('id', 0);
    if(0 < $id)
    {
        $trail->push(sprintf('工程 %d', $id), route('gantt/show', ['id' => $id ]));
        $trail->push('子タスク');
    } else {
        $trail->push('新規作成');
    }
});

// Home > Blog > [Category]
Breadcrumbs::for('category', function ($trail, $category) {
    $trail->parent('blog');
    $trail->push($category->title, route('category', $category->id));
});

// Home > Blog > [Category] > [Post]
Breadcrumbs::for('post', function ($trail, $post) {
    $trail->parent('category', $post->category);
    $trail->push($post->title, route('post', $post->id));
});
