( () => {

  const {
    ApiRegistry,
    CommonParams,
    setInRequest,
    getFromRequest,
    addBaseObjectCRUDEndpoints,
    currEndpoint,
    currRoute,
  } = Groundhogg.apiDocs
  const { root: apiRoot } = Groundhogg.api.routes.v4
  const { sprintf, __, _x, _n } = wp.i18n

  const {
    Fragment,
    Pg,
    Input,
    Textarea,
    InputRepeater,
  } = MakeEl

  ApiRegistry.add('relationships', {
    name: __('Relationships'),
    description: () => Fragment([
      Pg({}, __('Relationships are used to create associations between objects. Like between contacts and companies, or deals and contacts.', 'groundhogg')),
    ]),
    endpoints: Groundhogg.createRegistry(),
  })

  const identifiers = [
    {
      param: 'type',
      type: 'string',
      required: true,
      description: () => Pg({}, __('The route for the object you wish to reference.')),
    },
    {
      ...CommonParams.id('object'),
      description: () => Pg({}, __('The ID of object you wish to create the relationship for.')),
    },
  ]

  const childParams = [
    {
      param: 'child_id',
      description: () => Pg({}, __('The ID of the object to add as a child.', 'groundhogg')),
      type: 'int',
      required: true
    },
    {
      param: 'child_type',
      description: () => Pg({}, __('The type of object to add as a child.', 'groundhogg')),
      type: 'string',
      required: true
    },
  ]

  const parentParams = [
    {
      param: 'parent_id',
      description: () => Pg({}, __('The ID of the object to add as a parent.', 'groundhogg')),
      type: 'int',
      required: true
    },
    {
      param: 'parent_type',
      description: () => Pg({}, __('The type of object to add as a parent.', 'groundhogg')),
      type: 'string',
      required: true,
    },
  ]

  ApiRegistry.relationships.endpoints.add('read-children', {
    name: __('Fetch related children', 'groundhogg'),
    description: () => Pg({},
      __('Retrieve related child objects of a specific type.', 'groundhogg')),
    method: 'GET',
    endpoint: `${ apiRoot }/:type/:id/relationships`,
    identifiers,
    params: [
      childParams[1]
    ],
    request: {
      child_type: 'contact'
    },
    response: {
      status: 'success',
    },
  })

  ApiRegistry.relationships.endpoints.add('read-parents', {
    name: __('Fetch related parents', 'groundhogg'),
    description: () => Pg({},
      __('Retrieve related parent objects of a specific type.', 'groundhogg')),
    method: 'GET',
    endpoint: `${ apiRoot }/:type/:id/relationships`,
    identifiers,
    params: [
      parentParams[1]
    ],
    request: {
      parent_type: 'contact'
    },
    response: {
      status: 'success',
    },
  })

  ApiRegistry.relationships.endpoints.add('create-child', {
    name: __('Create a parent&rarr;child relationship', 'groundhogg'),
    description: () => Pg({},
      __('Creates a new parent&rarr;child relationship.', 'groundhogg')),
    method: 'POST',
    endpoint: `${ apiRoot }/:type/:id/relationships`,
    identifiers,
    params: childParams,
    request: {
      child_id: 1234,
      child_type: 'contact'
    },
    response: {
      status: 'success',
    },
  })

  ApiRegistry.relationships.endpoints.add('create-parent', {
    name: __('Create a child&rarr;parent relationship', 'groundhogg'),
    description: () => Pg({},
      __('Creates a new child&rarr;parent relationship.', 'groundhogg')),
    method: 'POST',
    endpoint: `${ apiRoot }/:type/:id/relationships`,
    identifiers,
    params: parentParams,
    request: {
      parent_id: 1234,
      parent_type: 'contact'
    },
    response: {
      status: 'success',
    },
  })

  ApiRegistry.relationships.endpoints.add('delete-child', {
    name: __('Delete a parent&rarr;child relationship', 'groundhogg'),
    description: () => Pg({},
      __('Deletes an existing parent&rarr;child relationship.', 'groundhogg')),
    method: 'DELETE',
    endpoint: `${ apiRoot }/:type/:id/relationships`,
    identifiers,
    params: childParams,
    request: {
      child_id: 1234,
      child_type: 'contact'
    },
    response: {
      status: 'success',
    },
  })


  ApiRegistry.relationships.endpoints.add('delete-parent', {
    name: __('Delete a child&rarr;parent relationship', 'groundhogg'),
    description: () => Pg({},
      __('Deletes an existing child&rarr;parent relationship.', 'groundhogg')),
    method: 'DELETE',
    endpoint: `${ apiRoot }/:type/:id/relationships`,
    identifiers,
    params: parentParams,
    request: {
      parent_id: 1234,
      parent_type: 'contact'
    },
    response: {
      status: 'success',
    },
  })

} )()
