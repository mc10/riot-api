class Region:
    REGIONS = ["BR", "EUNE", "EUW", "NA", "TR"];

    @classmethod
    def is_region(cls, region):
        return region in cls.REGIONS
