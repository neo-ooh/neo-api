<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPHeader.php
 */

namespace Neo\Modules\Properties\Documents\POP\components;

use Illuminate\View\Component;
use Neo\Modules\Properties\Documents\POP\POPRequest;

class POPHeader extends Component {
	public function __construct(protected string $title, protected POPRequest $request) {
	}

	public function render() {
		return view('properties::pop.header', [
			"title"         => $this->title,
			"contract_name" => $this->request->contract_number,
			"advertiser"    => $this->request->advertiser,
			"client"        => $this->request->client,
			"salesperson"   => $this->request->salesperson,
		])->render();
	}
}
